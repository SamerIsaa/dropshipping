<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Fulfillment\Clients\CJDropshippingClient;
use App\Domain\Fulfillment\Models\FulfillmentJob;
use App\Domain\Orders\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class PollCJFulfillmentStatus implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $fulfillmentJobId)
    {
    }

    public function handle(CJDropshippingClient $client): void
    {
        $job = FulfillmentJob::with(['orderItem.order', 'provider'])
            ->find($this->fulfillmentJobId);

        if (! $job || ! $job->external_reference) {
            return;
        }

        // Ensure this job is for CJ strategy
        if (! $job->provider || $job->provider->driver_class !== \App\Domain\Fulfillment\Strategies\CJDropshippingFulfillmentStrategy::class) {
            return;
        }

        $response = $client->orderStatus(['orderIds' => [$job->external_reference]]);
        $body = $response->json() ?? [];
        $data = Arr::get($body, 'data.0');

        if (! $data) {
            return;
        }

        $status = strtolower((string) ($data['status'] ?? ''));
        $trackingNumber = $data['trackingNumber'] ?? null;
        $trackingUrl = $data['trackingUrl'] ?? null;

        $job->status = match ($status) {
            'completed', 'success', 'fulfilled' => 'succeeded',
            'failed', 'cancelled' => 'failed',
            default => $job->status,
        };
        $job->external_reference = $job->external_reference ?? ($data['orderId'] ?? null);
        $job->last_error = $data['errorMsg'] ?? $job->last_error;
        $job->fulfilled_at = $job->status === 'succeeded' ? now() : $job->fulfilled_at;
        $job->save();

        if ($trackingNumber) {
            Shipment::updateOrCreate(
                ['order_item_id' => $job->order_item_id, 'tracking_number' => $trackingNumber],
                [
                    'carrier' => $data['carrier'] ?? null,
                    'tracking_url' => $trackingUrl,
                    'shipped_at' => $data['shippedAt'] ?? now(),
                    'raw_events' => $data['events'] ?? null,
                ]
            );
        }
    }
}
