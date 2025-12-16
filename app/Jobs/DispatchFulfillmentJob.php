<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Fulfillment\Services\FulfillmentService;
use App\Domain\Orders\Models\OrderItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchFulfillmentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int|array $backoff = 60;

    public function __construct(public int $orderItemId)
    {
    }

    public function handle(FulfillmentService $fulfillmentService): void
    {
        $orderItem = OrderItem::with([
            'order.shippingAddress',
            'order.billingAddress',
            'productVariant.product.defaultFulfillmentProvider',
            'fulfillmentProvider',
            'supplierProduct.fulfillmentProvider',
        ])->findOrFail($this->orderItemId);

        $providerRetryLimit = $orderItem->fulfillmentProvider?->retry_limit ?? $this->tries;

        if ($this->attempts() > $providerRetryLimit) {
            $this->fail(new \RuntimeException('Exceeded fulfillment retry limit for provider.'));
            return;
        }

        $fulfillmentService->dispatchOrderItem($orderItem);
    }
}
