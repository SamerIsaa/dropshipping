<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Domain\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class PaymentWebhookController extends Controller
{
    public function __invoke(string $provider, Request $request, PaymentService $paymentService): JsonResponse
    {
        $payload = $request->all();
        $eventId = $payload['event_id'] ?? $payload['id'] ?? $request->header('X-Event-Id');

        if (! $eventId) {
            return response()->json(['error' => 'Missing event id'], Response::HTTP_BAD_REQUEST);
        }

        $payment = $paymentService->handleWebhook($provider, $eventId, $payload);

        return response()->json([
            'success' => true,
            'payment_id' => $payment->id,
            'payment_status' => $payment->status,
            'order_id' => $payment->order_id,
            'order_payment_status' => $payment->order->payment_status,
        ]);
    }
}
