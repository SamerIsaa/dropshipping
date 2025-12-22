<?php

declare(strict_types=1);

namespace App\Notifications\System;

use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ManualNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param array<int, string|class-string> $channels
     */
    public function __construct(
        public string $title,
        public string $body,
        public ?string $actionUrl = null,
        public ?string $actionLabel = null,
        public array $channels = ['database', 'broadcast'],
    ) {
    }

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'action_url' => $this->actionUrl,
            'action_label' => $this->actionLabel,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage())
            ->subject($this->title)
            ->line($this->body);

        if ($this->actionUrl) {
            $mail->action($this->actionLabel ?: 'View update', $this->actionUrl);
        }

        return $mail;
    }

    public function toWhatsApp(object $notifiable): string
    {
        $action = $this->actionUrl ? " {$this->actionUrl}" : '';

        return trim("{$this->title} - {$this->body}{$action}");
    }
}
