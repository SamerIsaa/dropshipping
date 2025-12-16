<?php

declare(strict_types=1);

namespace App\Listeners\Orders;

use App\Events\Orders\RefundProcessed;
use App\Notifications\Orders\RefundProcessedNotification;
use Illuminate\Support\Facades\Notification;

class SendRefundProcessedNotification
{
    public function handle(RefundProcessed $event): void
    {
        $order = $event->order;
        $notifiable = $order->customer ?? $order->user;
        $notification = new RefundProcessedNotification(
            $order,
            $event->amount,
            $event->currency,
            $event->reason
        );

        if ($notifiable) {
            Notification::send($notifiable, $notification);
            return;
        }

        Notification::route('mail', $order->email)->notify($notification);
    }
}
