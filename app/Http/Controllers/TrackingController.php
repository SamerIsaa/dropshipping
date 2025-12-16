<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class TrackingController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $orderNumber = $request->input('number');
        $email = $request->input('email');

        if (! $orderNumber || ! $email) {
            return response()->json(['error' => 'Missing number or email'], Response::HTTP_BAD_REQUEST);
        }

        $order = Order::query()
            ->where('number', $orderNumber)
            ->where('email', $email)
            ->with(['orderItems.shipments.trackingEvents'])
            ->first();

        if (! $order) {
            return response()->json(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $shipments = $order->orderItems->flatMap(function ($item) {
            return $item->shipments->map(function ($shipment) use ($item) {
                return [
                    'order_item_id' => $item->id,
                    'tracking_number' => $shipment->tracking_number,
                    'carrier' => $shipment->carrier,
                    'tracking_url' => $shipment->tracking_url,
                    'shipped_at' => $shipment->shipped_at,
                    'delivered_at' => $shipment->delivered_at,
                    'events' => $shipment->trackingEvents
                        ->sortByDesc('occurred_at')
                        ->values()
                        ->map(function ($event) {
                            return [
                                'status_code' => $event->status_code,
                                'status_label' => $event->status_label,
                                'description' => $event->description,
                                'location' => $event->location,
                                'occurred_at' => $event->occurred_at,
                            ];
                        }),
                ];
            });
        })->values();

        return response()->json([
            'order_number' => $order->number,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'shipments' => $shipments,
        ]);
    }
}
