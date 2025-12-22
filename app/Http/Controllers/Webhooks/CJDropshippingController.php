<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Domain\Fulfillment\Models\FulfillmentJob;
use App\Domain\Orders\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CJDropshippingController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->verifySignature($request);

        $payload = $request->all();
        $externalId = Arr::get($payload, 'orderId') ?? Arr::get($payload, 'data.orderId');

        if (! $externalId) {
            return response()->json(['ok' => true]);
        }

        $job = FulfillmentJob::where('external_reference', $externalId)->first();
        if (! $job) {
            return response()->json(['ok' => true]);
        }

        $status = strtolower((string) (Arr::get($payload, 'status') ?? ''));
        $trackingNumber = Arr::get($payload, 'trackingNumber');
        $trackingUrl = Arr::get($payload, 'trackingUrl');

        $job->status = match ($status) {
            'completed', 'success', 'fulfilled' => 'succeeded',
            'failed', 'cancelled' => 'failed',
            default => $job->status,
        };
        $job->fulfilled_at = $job->status === 'succeeded' ? now() : $job->fulfilled_at;
        $job->last_error = Arr::get($payload, 'errorMsg', $job->last_error);
        $job->save();

        if ($trackingNumber) {
            Shipment::updateOrCreate(
                ['order_item_id' => $job->order_item_id, 'tracking_number' => $trackingNumber],
                [
                    'carrier' => Arr::get($payload, 'carrier'),
                    'tracking_url' => $trackingUrl,
                    'shipped_at' => Arr::get($payload, 'shippedAt') ?? now(),
                    'raw_events' => Arr::get($payload, 'events'),
                ]
            );
        }

        return response()->json(['ok' => true]);
    }

    private function verifySignature(Request $request): void
    {
        $secret = config('services.cj.webhook_secret');
        if (! $secret) {
            return;
        }

        $provided = $request->header('CJ-SIGN') ?? $request->header('cj-sign');
        if (! $provided) {
            abort(401, 'Missing CJ signature');
        }

        $computed = Str::lower(hash_hmac('sha256', $request->getContent(), $secret));
        if (! hash_equals($computed, Str::lower($provided))) {
            abort(401, 'Invalid CJ signature');
        }
    }
}
