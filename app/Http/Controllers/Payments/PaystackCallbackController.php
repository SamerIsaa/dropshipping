<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Domain\Payments\PaymentService;
use App\Infrastructure\Payments\Paystack\PaystackService;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaystackCallbackController
{
    public function __invoke(Request $request, PaystackService $paystack, PaymentService $paymentService): RedirectResponse
    {
        $reference = (string) $request->query('reference', '');

        if ($reference === '') {
            return redirect()->route('home')->withErrors(['payment' => 'Missing payment reference.']);
        }

        $payment = Payment::query()
            ->where('provider', 'paystack')
            ->where('provider_reference', $reference)
            ->first();

        if (! $payment) {
            return redirect()->route('home')->withErrors(['payment' => 'Payment not found.']);
        }

        try {
            $verification = $paystack->verify($reference);
        } catch (\Throwable $e) {
            return redirect()
                ->route('orders.confirmation', ['number' => $payment->order?->number ?? ''])
                ->withErrors(['payment' => 'Payment verification failed. Please try again.']);
        }

        $data = is_array($verification->data) ? $verification->data : [];
        $status = strtolower((string) ($data['status'] ?? ''));

        $payment->update([
            'meta' => array_merge($payment->meta ?? [], ['paystack' => $data]),
        ]);

        if ($status === 'success') {
            $paymentService->markAsPaid($payment);

            return redirect()
                ->route('orders.confirmation', ['number' => $payment->order->number])
                ->with('status', 'Payment confirmed.');
        }

        if (in_array($status, ['failed', 'abandoned'], true)) {
            $payment->update(['status' => 'failed']);
        }

        return redirect()
            ->route('orders.confirmation', ['number' => $payment->order->number])
            ->withErrors(['payment' => 'Payment not completed.']);
    }
}
