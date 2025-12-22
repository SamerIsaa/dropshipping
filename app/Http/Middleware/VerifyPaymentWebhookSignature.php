<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyPaymentWebhookSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $provider = (string) $request->route('provider');
        $payload = $request->getContent();

        if ($provider === 'paystack') {
            $secret = config('services.paystack.webhook_secret') ?: config('services.paystack.secret_key');

            if (! $secret) {
                abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Paystack webhook secret not configured.');
            }

            $signature = $request->header('X-Paystack-Signature');
            if (! $signature) {
                abort(Response::HTTP_UNAUTHORIZED, 'Missing Paystack webhook signature.');
            }

            $computed = hash_hmac('sha512', $payload, (string) $secret);

            if (! hash_equals($computed, (string) $signature)) {
                abort(Response::HTTP_UNAUTHORIZED, 'Invalid Paystack webhook signature.');
            }

            return $next($request);
        }

        $secret = config('services.payments.webhook_secret');

        if (! $secret) {
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Payment webhook secret not configured.');
        }

        $signature = $request->header('X-Signature');
        if (! $signature) {
            abort(Response::HTTP_UNAUTHORIZED, 'Missing webhook signature.');
        }

        $computed = hash_hmac('sha256', $payload, (string) $secret);

        if (! hash_equals($computed, (string) $signature)) {
            abort(Response::HTTP_UNAUTHORIZED, 'Invalid webhook signature.');
        }

        return $next($request);
    }
}
