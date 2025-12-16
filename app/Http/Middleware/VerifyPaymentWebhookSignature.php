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
        $secret = config('services.payments.webhook_secret');

        if (! $secret) {
            return $next($request);
        }

        $signature = $request->header('X-Signature');
        $payload = $request->getContent();
        $computed = hash_hmac('sha256', $payload, $secret);

        if (! hash_equals($computed, (string) $signature)) {
            abort(Response::HTTP_UNAUTHORIZED, 'Invalid webhook signature.');
        }

        return $next($request);
    }
}
