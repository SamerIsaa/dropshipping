<?php

declare(strict_types=1);

namespace App\Domain\Payments;

use App\Domain\Orders\Models\Order;
use App\Domain\Payments\Models\Payment;
use App\Domain\Payments\Models\PaymentWebhook;
use App\Domain\Observability\EventLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

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

            $this->logger->payment($payment, 'webhook', $payload['status'] ?? 'pending', null, $payload);

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
        if ($payment->status === 'paid') {
            return $payment;
        }

        $payment->fill([
            'status' => 'paid',
            'paid_at' => now(),
        ])->save();

        $payment->order->update(['payment_status' => 'paid']);

        $this->logger->payment($payment, 'payment', 'paid', 'Payment marked as paid');

        return $payment->refresh();
    }

    private function resolvePaymentFromPayload(string $provider, array $payload): Payment
    {
        $providerReference = $payload['provider_reference'] ?? $payload['transaction_id'] ?? null;
        $orderNumber = $payload['order_number'] ?? null;
        $amount = $payload['amount'] ?? null;
        $currency = $payload['currency'] ?? 'USD';
        $idempotencyKey = $payload['idempotency_key'] ?? $payload['event_id'] ?? null;

        if (! $orderNumber) {
            throw new RuntimeException('Order number missing in webhook payload');
        }

        /** @var Order $order */
        $order = Order::where('number', $orderNumber)->firstOrFail();

        $payment = Payment::firstOrCreate(
            [
                'provider' => $provider,
                'provider_reference' => $providerReference,
            ],
            [
                'order_id' => $order->id,
                'status' => 'pending',
                'amount' => $amount ?? $order->grand_total,
                'currency' => $currency,
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
}
