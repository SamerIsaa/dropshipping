<?php

declare(strict_types=1);

namespace App\Domain\Payments;

use App\Domain\Orders\Models\Order;
use App\Domain\Payments\Models\Payment;
use App\Domain\Payments\Models\PaymentWebhook;
use App\Domain\Observability\EventLogger;
use App\Events\Orders\OrderPaid;
use App\Jobs\DispatchFulfillmentJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use InvalidArgumentException;

class PaymentService
{
    public function __construct(private readonly EventLogger $logger)
    {
    }

    /**
     * Handle incoming webhook in an idempotent way and update payment + order payment_status.
     */
    public function handleWebhook(string $provider, string $eventId, array $payload): Payment
    {
        return DB::transaction(function () use ($provider, $eventId, $payload) {
            $this->assertPayloadHasBasics($payload);

            $webhook = PaymentWebhook::firstOrCreate(
                ['external_event_id' => $eventId],
                [
                    'provider' => $provider,
                    'payload' => $payload,
                    'processed_at' => null,
                ]
            );

            // If already processed, short-circuit to prevent double confirmation
            if ($webhook->processed_at) {
                return $webhook->payment ?? $this->resolvePaymentFromPayload($provider, $payload);
            }

            $payment = $this->resolvePaymentFromPayload($provider, $payload);

            $this->applyStatusFromPayload($payment, $payload);

            $this->logger->payment($payment, 'webhook', strtolower($payload['status'] ?? 'pending'), null, $payload);

            $webhook->payment()->associate($payment);
            $webhook->processed_at = now();
            $webhook->save();

            return $payment;
        });
    }

    /**
     * Confirm a payment and update order payment status without altering fulfillment status.
     */
    public function markAsPaid(Payment $payment): Payment
    {
        $wasPaid = $payment->status === 'paid';

        if (! $wasPaid) {
            $payment->fill([
                'status' => 'paid',
                'paid_at' => now(),
            ])->save();
        }

        $order = $payment->order()->first();

        if ($order) {
            $order->payment_status = 'paid';
            if ($order->status === 'pending') {
                $order->status = 'paid';
            }
            $order->save();
            $this->dispatchFulfillmentForOrder($order);

            if (! $wasPaid) {
                event(new OrderPaid($order));
            }
        }

        $this->logger->payment($payment, 'payment', 'paid', 'Payment marked as paid');

        return $payment->refresh();
    }

    private function resolvePaymentFromPayload(string $provider, array $payload): Payment
    {
        $providerReference = $payload['provider_reference'] ?? $payload['transaction_id'] ?? null;
        $orderNumber = $payload['order_number'] ?? null;
        $amount = $payload['amount'] ?? null;
        $currency = $payload['currency'] ?? null;
        $idempotencyKey = $payload['idempotency_key'] ?? $payload['event_id'] ?? null;

        $this->assertPayloadHasBasics($payload);

        /** @var Order $order */
        $order = Order::where('number', $orderNumber)->firstOrFail();

        $this->assertTotalsMatch($order, $amount, $currency);

        $payment = Payment::firstOrCreate(
            [
                'provider' => $provider,
                'provider_reference' => $providerReference,
            ],
            [
                'order_id' => $order->id,
                'status' => 'pending',
                'amount' => $amount ?? $order->grand_total,
                'currency' => $currency ?? $order->currency,
                'meta' => $payload,
                'idempotency_key' => $idempotencyKey,
            ]
        );

        // keep idempotency key synced even if payment existed
        if ($idempotencyKey && $payment->idempotency_key !== $idempotencyKey) {
            $payment->forceFill(['idempotency_key' => $idempotencyKey])->save();
        }

        return $payment;
    }

    private function applyStatusFromPayload(Payment $payment, array $payload): void
    {
        $status = strtolower($payload['status'] ?? 'pending');

        if (in_array($status, ['paid', 'captured', 'success', 'succeeded'], true)) {
            $this->markAsPaid($payment);
            return;
        }

        if (in_array($status, ['failed', 'declined'], true)) {
            $payment->update(['status' => 'failed']);
            Log::warning('Payment failed', ['payment_id' => $payment->id, 'payload' => $payload]);
            return;
        }

        if ($status === 'authorized') {
            $payment->update(['status' => 'authorized']);
        }
    }

    private function assertPayloadHasBasics(array $payload): void
    {
        if (empty($payload['order_number'])) {
            throw new RuntimeException('Order number missing in webhook payload');
        }

        if (! isset($payload['amount']) || ! is_numeric($payload['amount'])) {
            throw new RuntimeException('Amount missing or invalid in webhook payload');
        }

        if (empty($payload['currency'])) {
            throw new RuntimeException('Currency missing in webhook payload');
        }
    }

    private function assertTotalsMatch(Order $order, float|string|null $amount, ?string $currency): void
    {
        $numericAmount = (float) $amount;
        if ($numericAmount <= 0) {
            throw new InvalidArgumentException('Amount must be positive.');
        }

        if (strcasecmp((string) $currency, $order->currency) !== 0) {
            throw new InvalidArgumentException('Currency mismatch for order.');
        }

        if (abs($numericAmount - (float) $order->grand_total) > 0.01) {
            throw new InvalidArgumentException('Amount does not match order total.');
        }
    }

    private function dispatchFulfillmentForOrder(Order $order): void
    {
        $order->loadMissing([
            'orderItems.fulfillmentProvider',
            'orderItems.supplierProduct.fulfillmentProvider',
            'orderItems.productVariant.product.defaultFulfillmentProvider',
        ]);

        $dispatched = false;

        foreach ($order->orderItems as $item) {
            if (in_array($item->fulfillment_status, ['fulfilled', 'failed', 'cancelled'], true)) {
                continue;
            }

            if ($item->fulfillmentJob()->exists()) {
                continue;
            }

            $hasProvider = $item->fulfillmentProvider
                || $item->supplierProduct?->fulfillmentProvider
                || $item->productVariant?->product?->defaultFulfillmentProvider;

            if (! $hasProvider) {
                Log::warning('Skipping fulfillment dispatch; no provider resolved.', [
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                ]);
                continue;
            }

            DispatchFulfillmentJob::dispatch($item->id);
            $item->update(['fulfillment_status' => 'fulfilling']);
            $dispatched = true;
        }

        if ($dispatched && ! in_array($order->status, ['fulfilled', 'cancelled', 'refunded'], true)) {
            $order->update(['status' => 'fulfilling']);
        }
    }
}
