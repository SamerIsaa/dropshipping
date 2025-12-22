<?php

declare(strict_types=1);

namespace App\Infrastructure\Payments\Paystack;

use App\Domain\Orders\Models\Order;
use App\Domain\Payments\Models\Payment;
use App\Services\Api\ApiClient;
use App\Services\Api\ApiException;
use App\Services\Api\ApiResponse;
use RuntimeException;

class PaystackService
{
    private ApiClient $client;
    private string $publicKey;

    public function __construct()
    {
        $config = config('services.paystack', []);
        $secret = (string) ($config['secret_key'] ?? '');

        if ($secret === '') {
            throw new RuntimeException('Paystack secret key is not configured.');
        }

        $this->publicKey = (string) ($config['public_key'] ?? '');
        $baseUrl = rtrim((string) ($config['base_url'] ?? 'https://api.paystack.co'), '/');
        $this->client = (new ApiClient($baseUrl, ['Accept' => 'application/json']))->withToken($secret);
    }

    public function publicKey(): string
    {
        return $this->publicKey;
    }

    public function initialize(Order $order, Payment $payment, array $customer, string $paymentMethod): ApiResponse
    {
        $reference = $payment->provider_reference;
        if (! $reference) {
            throw new RuntimeException('Payment reference is missing.');
        }

        $payload = [
            'email' => $customer['email'] ?? $order->email,
            'amount' => (int) round((float) $order->grand_total * 100),
            'currency' => $order->currency ?? 'USD',
            'reference' => $reference,
            'callback_url' => route('payments.paystack.callback'),
            'metadata' => [
                'order_number' => $order->number,
                'payment_id' => $payment->id,
                'customer_id' => $order->customer_id,
                'payment_method' => $paymentMethod,
            ],
        ];

        if ($paymentMethod === 'mobile_money') {
            $payload['channels'] = ['mobile_money'];
        }

        $response = $this->client->post('/transaction/initialize', $payload);

        return $this->unwrap($response);
    }

    public function verify(string $reference): ApiResponse
    {
        $response = $this->client->get('/transaction/verify/' . urlencode($reference));

        return $this->unwrap($response);
    }

    private function unwrap(ApiResponse $response): ApiResponse
    {
        $payload = is_array($response->data) ? $response->data : [];
        $status = (bool) ($payload['status'] ?? false);

        if (! $status) {
            $message = is_array($payload) ? ($payload['message'] ?? 'Payment API error') : 'Payment API error';
            throw new ApiException($message, $response->status, null, $payload);
        }

        return ApiResponse::success($payload['data'] ?? null, $payload, $payload['message'] ?? null, $response->status);
    }
}
