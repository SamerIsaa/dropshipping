<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Domain\Fulfillment\Models\FulfillmentJob;
use App\Domain\Fulfillment\Strategies\CJDropshippingFulfillmentStrategy;
use App\Domain\Orders\Models\Shipment;
use App\Domain\Products\Services\CjProductImportService;
use App\Http\Controllers\Controller;
use App\Models\CJWebhookLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CJWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $this->verifySignature($request);

        $payload = $request->json()->all();
        if ($payload === []) {
            $payload = $request->all();
        }

        $log = CJWebhookLog::create([
            'message_id' => $payload['messageId'] ?? null,
            'type' => $payload['type'] ?? null,
            'message_type' => $payload['messageType'] ?? null,
            'payload' => $payload,
        ]);

        Log::info('CJ webhook received', [
            'id' => $log->id,
            'message_id' => $log->message_id,
            'type' => $log->type,
            'message_type' => $log->message_type,
        ]);

        $this->handleOrderStatus($payload);
        $this->handleProductSync($payload);

        // Keep response under 3 seconds — defer heavy work to queues if needed.
        return response()->json(['ok' => true]);
    }

    private function handleOrderStatus(array $payload): void
    {
        $externalId = Arr::get($payload, 'orderId') ?? Arr::get($payload, 'data.orderId');

        if (! $externalId) {
            return;
        }

        $job = FulfillmentJob::with('provider')->where('external_reference', $externalId)->first();
        if (! $job || $job->provider?->driver_class !== CJDropshippingFulfillmentStrategy::class) {
            return;
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
    }

    private function handleProductSync(array $payload): void
    {
        $orderId = $this->extractValue($payload, ['orderId', 'data.orderId']);
        if ($orderId) {
            return;
        }

        $pid = $this->extractValue($payload, ['pid', 'productId', 'product_id', 'data.pid', 'data.productId']);
        $productSku = $this->extractValue($payload, ['productSku', 'productSKU', 'data.productSku', 'data.productSKU']);
        $variantSku = $this->extractValue($payload, ['variantSku', 'variantSKU', 'data.variantSku', 'data.variantSKU', 'sku', 'data.sku']);

        if (! $pid && ! $productSku && ! $variantSku) {
            return;
        }

        $importer = app(CjProductImportService::class);

        $lookupType = $pid ? 'pid' : ($productSku ? 'productSku' : 'variantSku');
        $lookupValue = $pid ?: ($productSku ?: $variantSku);

        if (! $lookupValue) {
            return;
        }

        try {
            $importer->importByLookup($lookupType, $lookupValue, [
                'respectSyncFlag' => true,
                'respectLocks' => true,
                'syncImages' => true,
                'syncVariants' => true,
            ]);
        } catch (\Throwable $e) {
            Log::warning('CJ webhook product sync failed', [
                'lookup_type' => $lookupType,
                'lookup_value' => $lookupValue,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function extractValue(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = Arr::get($payload, $key);
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
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
