<?php

declare(strict_types=1);

namespace App\Domain\Fulfillment\Services;

use App\Domain\Fulfillment\Clients\CJDropshippingClient;
use App\Domain\Fulfillment\Exceptions\FulfillmentException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class CJFreightService
{
    public function __construct(private readonly CJDropshippingClient $client)
    {
    }

    /**
     * Calculate shipping quote for a destination and line items.
     *
     * @param array{country:string,province?:string,city?:string,zip?:string} $destination
     * @param array<int,array{sku:string,quantity:int,weight?:float}> $items
     * @param array $options Additional CJ payload fields (warehouseId, logisticsType, shippingMethod, etc.)
     */
    public function quote(array $destination, array $items, array $options = []): array
    {
        $products = collect($items)
            ->map(function (array $item) {
                return [
                    'quantity' => (int) ($item['quantity'] ?? 0),
                    'vid' => $item['vid'] ?? null,
                ];
            })
            ->filter(fn (array $p) => $p['quantity'] > 0 && ! empty($p['vid']))
            ->values()
            ->all();

        if (empty($products)) {
            throw new FulfillmentException('CJ freight quote failed: no variant IDs available for freight calculation.');
        }

        $payload = array_merge([
            'startCountryCode' => strtoupper((string) ($options['from_country'] ?? $options['from_country_code'] ?? $options['ship_from'] ?? 'CN')),
            'endCountryCode' => strtoupper((string) $destination['country']),
            'zip' => $destination['zip'] ?? null,
            'products' => $products,
        ], Arr::only($options, ['taxId', 'houseNumber', 'iossNumber']));

        try {
            $data = $this->callFreight($payload);
            if (empty($data)) {
                $data = $this->callFreight($payload, true); // fallback to tip endpoint
            }

            return $data;
        } catch (\Throwable $e) {
            Log::warning('CJ freight quote failed', [
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);
            throw new FulfillmentException('CJ freight quote failed: ' . $e->getMessage(), 0, $e);
        }
    }

    private function callFreight(array $payload, bool $useTip = false): array
    {
        $response = $useTip
            ? $this->client->freightCalculateTip(['reqDTOS' => [$payload]])
            : $this->client->freightCalculate($payload);

        $data = $response->data ?? [];
        if (! is_array($data)) {
            throw new FulfillmentException('CJ freight quote error: invalid response data type.');
        }

        return $data;
    }
}
