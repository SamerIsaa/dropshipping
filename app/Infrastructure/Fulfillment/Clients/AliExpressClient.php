<?php

declare(strict_types=1);

namespace App\Infrastructure\Fulfillment\Clients;

use App\Domain\Fulfillment\Exceptions\FulfillmentException;
use App\Domain\Fulfillment\Models\FulfillmentProvider;
use App\Domain\Fulfillment\DTOs\FulfillmentRequestData;
use Illuminate\Support\Facades\Http;

class AliExpressClient
{
    public function __construct(private readonly FulfillmentProvider $provider)
    {
    }

    /**
        * Dispatch an order to AliExpress.
        * In a real implementation, map credentials/settings to the AliExpress API contract.
        */
    public function createOrder(FulfillmentRequestData $request): array
    {
        $baseUrl = $this->provider->settings['base_url'] ?? 'https://api.aliexpress.com';
        $token = $this->provider->credentials['access_token'] ?? null;

        if (! $token) {
            throw new FulfillmentException('AliExpress credentials are missing.');
        }

        $payload = [
            'order_item_id' => $request->orderItem->id,
            'quantity' => $request->orderItem->quantity,
            'sku' => $request->supplierProduct?->external_sku ?? $request->orderItem->source_sku,
            'shipping_address' => $request->shippingAddress?->toArray(),
            'billing_address' => $request->billingAddress?->toArray(),
            'options' => $request->options,
        ];

        $response = Http::baseUrl($baseUrl)
            ->timeout(20)
            ->withToken($token)
            ->post('/orders', $payload);

        if ($response->failed()) {
            throw new FulfillmentException(
                'AliExpress order creation failed: '.$response->body()
            );
        }

        return $response->json();
    }
}
