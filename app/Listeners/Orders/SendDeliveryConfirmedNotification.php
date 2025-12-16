<?php

declare(strict_types=1);

namespace App\Listeners\Orders;

use App\Events\Orders\OrderDelivered;
use App\Notifications\Orders\DeliveryConfirmedNotification;
use Illuminate\Support\Facades\Notification;

class SendDeliveryConfirmedNotification
{
    public function handle(OrderDelivered $event): void
    {
        $order = $event->order;
        $notifiable = $order->customer ?? $order->user;
        $notification = new DeliveryConfirmedNotification($order, $event->deliveredAt);

        if ($notifiable) {
            Notification::send($notifiable, $notification);
            return;
        }

        Notification::route('mail', $order->email)->notify($notification);
    }
}
