<?php

declare(strict_types=1);

namespace App\Listeners\Orders;

use App\Events\Orders\OrderPaid;
use App\Events\Orders\OrderPlaced;
use App\Notifications\Orders\OrderConfirmedNotification;
use Illuminate\Support\Facades\Notification;

class SendOrderConfirmedNotification
{
    public function handle(OrderPlaced|OrderPaid $event): void
    {
        $order = $event->order;
        $notifiable = $order->customer ?? $order->user;

        if ($notifiable) {
            Notification::send($notifiable, new OrderConfirmedNotification($order));
            return;
        }

        Notification::route('mail', $order->email)
            ->notify(new OrderConfirmedNotification($order));
    }
}
