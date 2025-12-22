<?php

declare(strict_types=1);

namespace App\Listeners\Orders;

use App\Events\Orders\OrderPaid;
use App\Events\Orders\OrderPlaced;
use App\Models\User;
use App\Notifications\AdminOrderEventNotification;
use App\Notifications\Orders\OrderConfirmedNotification;
use Illuminate\Support\Facades\Notification;

class SendOrderConfirmedNotification
{
    public function handle(OrderPlaced|OrderPaid $event): void
    {
        $order = $event->order;

        if ($order->payment_status !== 'paid') {
            return;
        }

        $notifiable = $order->customer ?? $order->user;

        if ($notifiable) {
            Notification::send($notifiable, new OrderConfirmedNotification($order));
        }

        if (! $notifiable) {
            Notification::route('mail', $order->email)
                ->notify(new OrderConfirmedNotification($order));
        }

        $this->notifyAdmins($order);
    }

    private function notifyAdmins($order): void
    {
        $admins = User::query()
            ->whereIn('role', ['admin', 'staff'])
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send(
            $admins,
            new AdminOrderEventNotification($order, 'Order paid', 'Customer payment confirmed.')
        );
    }
}
