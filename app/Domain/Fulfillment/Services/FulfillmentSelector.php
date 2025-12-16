<?php

declare(strict_types=1);

namespace App\Domain\Fulfillment\Services;

use App\Domain\Fulfillment\Contracts\FulfillmentStrategy;
use App\Domain\Fulfillment\Exceptions\FulfillmentException;
use App\Domain\Fulfillment\Models\FulfillmentProvider;
use App\Domain\Orders\Models\OrderItem;
use Illuminate\Contracts\Container\Container;

class FulfillmentSelector
{
    public function __construct(
        private readonly Container $container,
        private readonly SupplierDecisionService $decisionService,
    ) {
    }

    public function resolveForOrderItem(OrderItem $orderItem): FulfillmentStrategy
    {
        $provider = $this->determineProvider($orderItem);

        if (! class_exists($provider->driver_class)) {
            throw new FulfillmentException("Fulfillment driver not found for provider [{$provider->code}]");
        }

        /** @var FulfillmentStrategy $strategy */
        $strategy = $this->container->make($provider->driver_class, ['provider' => $provider]);

        return $strategy;
    }

    private function determineProvider(OrderItem $orderItem): FulfillmentProvider
    {
        $provider = $orderItem->fulfillmentProvider
            ?? $orderItem->supplierProduct?->fulfillmentProvider
            ?? $orderItem->productVariant?->product?->defaultFulfillmentProvider;

        if ($provider && $provider->is_active && ! $provider->is_blacklisted) {
            return $provider;
        }

        return $this->decisionService->selectProvider($orderItem);
    }
}
