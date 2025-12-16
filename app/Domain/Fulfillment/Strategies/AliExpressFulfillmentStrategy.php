<?php

declare(strict_types=1);

namespace App\Domain\Fulfillment\Strategies;

use App\Domain\Fulfillment\Contracts\FulfillmentStrategy;
use App\Domain\Fulfillment\DTOs\FulfillmentRequestData;
use App\Domain\Fulfillment\DTOs\FulfillmentResult;
use App\Domain\Fulfillment\Exceptions\FulfillmentException;
use App\Domain\Fulfillment\Models\FulfillmentProvider;
use App\Infrastructure\Fulfillment\Clients\AliExpressClient;

class AliExpressFulfillmentStrategy implements FulfillmentStrategy
{
    public function __construct(
        private readonly FulfillmentProvider $provider,
        private readonly AliExpressClient $client,
    ) {
    }

    public function dispatch(FulfillmentRequestData $request): FulfillmentResult
    {
        try {
            $response = $this->client->createOrder($request);
        } catch (FulfillmentException $e) {
            return new FulfillmentResult(
                status: 'failed',
                rawResponse: ['error' => $e->getMessage()]
            );
        }

        $status = match ($response['status'] ?? null) {
            'shipped', 'fulfilled' => 'succeeded',
            'processing', 'paid' => 'in_progress',
            default => 'in_progress',
        };

        return new FulfillmentResult(
            status: $status,
            externalReference: $response['order_id'] ?? null,
            trackingNumber: $response['tracking_number'] ?? null,
            trackingUrl: $response['tracking_url'] ?? null,
            rawResponse: $response ?? [],
        );
    }
}
