<?php

declare(strict_types=1);

namespace App\Listeners\Orders;

use App\Events\Orders\CustomsUpdated;
use App\Notifications\Orders\CustomsInfoNotification;
use Illuminate\Support\Facades\Notification;

class SendCustomsInfoNotification
{
    public function handle(CustomsUpdated $event): void
    {
        $order = $event->order;
        $notifiable = $order->customer ?? $order->user;
        $notification = new CustomsInfoNotification($order, $event->note);

        if ($notifiable) {
            Notification::send($notifiable, $notification);
            return;
        }

        Notification::route('mail', $order->email)->notify($notification);
    }
}
