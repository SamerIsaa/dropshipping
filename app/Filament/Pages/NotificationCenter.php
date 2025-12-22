<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\User;
use App\Notifications\Channels\WhatsAppChannel;
use App\Notifications\System\ManualNotification;
use BackedEnum;
use Filament\Notifications\Notification;
use App\Filament\Pages\BasePage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use UnitEnum;

class NotificationCenter extends BasePage
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-paper-airplane';
    protected static UnitEnum|string|null $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 96;

    protected string $view = 'filament.pages.notification-center';

    public string $notificationTitle = '';
    public string $body = '';
    public ?string $actionUrl = null;
    public ?string $actionLabel = null;
    public string $audience = 'customers';
    public bool $sendToAll = true;
    public string $recipientEmails = '';
    public bool $sendDatabase = true;
    public bool $sendPush = true;
    public bool $sendMail = false;
    public bool $sendWhatsApp = false;

    public function send(): void
    {
        $this->validate([
            'notificationTitle' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:1000'],
            'actionUrl' => ['nullable', 'url', 'max:500'],
            'actionLabel' => ['nullable', 'string', 'max:80'],
            'audience' => ['required', 'in:customers,admins,both'],
            'recipientEmails' => ['nullable', 'string', 'max:2000'],
            'sendDatabase' => ['boolean'],
            'sendPush' => ['boolean'],
            'sendMail' => ['boolean'],
            'sendWhatsApp' => ['boolean'],
        ]);

        $channels = $this->buildChannels();
        if (empty($channels)) {
            Notification::make()
                ->title('Choose at least one channel')
                ->danger()
                ->send();
            return;
        }

        $recipients = $this->resolveRecipients();
        if ($recipients->isEmpty()) {
            Notification::make()
                ->title('No recipients found')
                ->warning()
                ->send();
            return;
        }

        $notification = new ManualNotification(
            title: $this->notificationTitle,
            body: $this->body,
            actionUrl: $this->actionUrl,
            actionLabel: $this->actionLabel,
            channels: $channels,
        );

        $recipients->chunk(200)->each(function (Collection $chunk) use ($notification) {
            NotificationFacade::send($chunk, $notification);
        });

        $this->reset(['notificationTitle', 'body', 'actionUrl', 'actionLabel', 'recipientEmails']);

        Notification::make()
            ->title('Notification sent')
            ->success()
            ->send();
    }

    /**
     * @return array<int, string|class-string>
     */
    private function buildChannels(): array
    {
        $channels = [];

        if ($this->sendDatabase) {
            $channels[] = 'database';
        }

        if ($this->sendPush) {
            $channels[] = 'broadcast';
        }

        if ($this->sendMail) {
            $channels[] = 'mail';
        }

        if ($this->sendWhatsApp) {
            $channels[] = WhatsAppChannel::class;
        }

        return $channels;
    }

    /**
     * @return Collection<int, \Illuminate\Contracts\Auth\Authenticatable>
     */
    private function resolveRecipients(): Collection
    {
        $emails = collect(preg_split('/[\s,]+/', $this->recipientEmails ?? '', -1, PREG_SPLIT_NO_EMPTY))
            ->filter()
            ->unique()
            ->values();

        $recipients = collect();

        if ($this->audience === 'customers' || $this->audience === 'both') {
            if ($this->sendToAll && $emails->isEmpty()) {
                $recipients = $recipients->merge(Customer::query()->get());
            } elseif ($emails->isNotEmpty()) {
                $recipients = $recipients->merge(Customer::query()->whereIn('email', $emails)->get());
            }
        }

        if ($this->audience === 'admins' || $this->audience === 'both') {
            $adminQuery = User::query()->whereIn('role', ['admin', 'staff']);
            if ($emails->isNotEmpty()) {
                $adminQuery->whereIn('email', $emails);
            }
            $recipients = $recipients->merge($adminQuery->get());
        }

        return $recipients->unique('email')->values();
    }
}

