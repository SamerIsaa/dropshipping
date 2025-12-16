<?php

declare(strict_types=1);

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;

class WhatsAppChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $phone = $notifiable->phone ?? null;
        if (! $phone) {
            return;
        }

        $message = $notification->toWhatsApp($notifiable);

        // TODO: integrate with a WhatsApp provider (e.g., Twilio, Vonage, Meta Cloud API).
        // Example placeholder:
        // Http::post(config('services.whatsapp.endpoint'), ['to' => $phone, 'body' => $message]);
    }
}
