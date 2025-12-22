<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user('customer');

        $perPage = 12;
        $orders = Order::query()
            ->where('customer_id', $user->id)
            ->latest('placed_at')
            ->paginate($perPage)
            ->through(function (Order $order) {
                return [
                    'id' => $order->id,
                    'number' => $order->number,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'grand_total' => $order->grand_total,
                    'currency' => $order->currency,
                    'placed_at' => $order->placed_at,
                    'email' => $order->email,
                ];
            });

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, Order $order): Response
    {
        $customer = $request->user('customer');
        if (! $customer || $order->customer_id !== $customer->id) {
            abort(404);
        }

        $order->load([
            'shippingAddress',
            'billingAddress',
            'orderItems.productVariant.product',
            'orderItems.review',
            'orderItems.returnRequest',
            'orderItems.shipments.trackingEvents',
            'payments',
        ]);

        return Inertia::render('Orders/Show', [
            'order' => [
                'id' => $order->id,
                'number' => $order->number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'currency' => $order->currency,
                'subtotal' => $order->subtotal,
                'shipping_total' => $order->shipping_total,
                'tax_total' => $order->tax_total,
                'discount_total' => $order->discount_total,
                'grand_total' => $order->grand_total,
                'placed_at' => $order->placed_at,
                'delivery_notes' => $order->delivery_notes,
                'shippingAddress' => $order->shippingAddress ? [
                    'name' => $order->shippingAddress->name,
                    'line1' => $order->shippingAddress->line1,
                    'line2' => $order->shippingAddress->line2,
                    'city' => $order->shippingAddress->city,
                    'state' => $order->shippingAddress->state,
                    'postal_code' => $order->shippingAddress->postal_code,
                    'country' => $order->shippingAddress->country,
                    'phone' => $order->shippingAddress->phone,
                ] : null,
                'billingAddress' => $order->billingAddress ? [
                    'name' => $order->billingAddress->name,
                    'line1' => $order->billingAddress->line1,
                    'line2' => $order->billingAddress->line2,
                    'city' => $order->billingAddress->city,
                    'state' => $order->billingAddress->state,
                    'postal_code' => $order->billingAddress->postal_code,
                    'country' => $order->billingAddress->country,
                ] : null,
                'items' => $order->orderItems->map(function ($item) {
                    $product = $item->productVariant?->product;
                    return [
                        'id' => $item->id,
                        'name' => $item->snapshot['name'] ?? 'Item',
                        'variant' => $item->snapshot['variant'] ?? null,
                        'product_id' => $product?->id,
                        'product_slug' => $product?->slug,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total' => $item->total,
                        'fulfillment_status' => $item->fulfillment_status,
                        'review' => $item->review ? [
                            'id' => $item->review->id,
                            'rating' => $item->review->rating,
                            'title' => $item->review->title,
                            'body' => $item->review->body,
                            'status' => $item->review->status,
                            'created_at' => $item->review->created_at,
                        ] : null,
                        'return_request' => $item->returnRequest ? [
                            'id' => $item->returnRequest->id,
                            'status' => $item->returnRequest->status,
                            'reason' => $item->returnRequest->reason,
                            'notes' => $item->returnRequest->notes,
                            'created_at' => $item->returnRequest->created_at,
                        ] : null,
                        'shipments' => $item->shipments->map(function ($shipment) {
                            return [
                                'id' => $shipment->id,
                                'tracking_number' => $shipment->tracking_number,
                                'carrier' => $shipment->carrier,
                                'tracking_url' => $shipment->tracking_url,
                                'shipped_at' => $shipment->shipped_at,
                                'delivered_at' => $shipment->delivered_at,
                                'events' => $shipment->trackingEvents->map(fn ($event) => [
                                    'id' => $event->id,
                                    'status_label' => $event->status_label,
                                    'description' => $event->description,
                                    'location' => $event->location,
                                    'occurred_at' => $event->occurred_at,
                                ]),
                            ];
                        }),
                    ];
                }),
                'payments' => $order->payments->map(fn ($payment) => [
                    'id' => $payment->id,
                    'provider' => $payment->provider,
                    'status' => $payment->status,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'provider_reference' => $payment->provider_reference,
                    'paid_at' => $payment->paid_at,
                ]),
            ],
        ]);
    }
}
