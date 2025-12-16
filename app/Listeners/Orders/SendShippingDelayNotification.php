<?php

declare(strict_types=1);

namespace App\Listeners\Orders;

use App\Events\Orders\FulfillmentDelayed;
use App\Notifications\Orders\ShippingDelayNotification;
use Illuminate\Support\Facades\Notification;

class SendShippingDelayNotification
{
    public function handle(FulfillmentDelayed $event): void
    {
        $order = $event->order;
        $notifiable = $order->customer ?? $order->user;

        $notification = new ShippingDelayNotification($order, $event->eta, $event->reason);

        if ($notifiable) {
            Notification::send($notifiable, $notification);
            return;
        }

        Notification::route('mail', $order->email)->notify($notification);
    }
}
