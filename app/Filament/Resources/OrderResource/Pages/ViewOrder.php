<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Domain\Orders\Models\OrderAuditLog;
use App\Domain\Observability\EventLogger;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('copyShipping')
                ->label('Copy Shipping Info')
                ->color('gray')
                ->icon('heroicon-o-clipboard')
                ->copyable(fn (Order $record) => implode(', ', array_filter([
                    $record->shippingAddress?->name,
                    $record->shippingAddress?->line1,
                    $record->shippingAddress?->line2,
                    $record->shippingAddress?->city,
                    $record->shippingAddress?->state,
                    $record->shippingAddress?->postal_code,
                    $record->shippingAddress?->country,
                    $record->shippingAddress?->phone,
                ]))),
            Actions\Action::make('overrideStatus')
                ->label('Manual Status Override')
                ->icon('heroicon-o-adjustments-vertical')
                ->color('warning')
                ->form([
                    Actions\Components\Select::make('status')->label('Order Status')->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'fulfilling' => 'Fulfilling',
                        'fulfilled' => 'Fulfilled',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                    ])->required(),
                    Actions\Components\Select::make('payment_status')->label('Payment Status')->options([
                        'unpaid' => 'Unpaid',
                        'paid' => 'Paid',
                        'refunded' => 'Refunded',
                    ])->required(),
                    Actions\Components\Textarea::make('note')->rows(3)->required(),
                ])
                ->action(function (Order $record, array $data): void {
                    $record->update([
                        'status' => $data['status'],
                        'payment_status' => $data['payment_status'],
                    ]);
                    OrderAuditLog::create([
                        'order_id' => $record->id,
                        'user_id' => auth()->id(),
                        'action' => 'order_override',
                        'note' => $data['note'],
                        'payload' => $data,
                    ]);
                    app(EventLogger::class)->order(
                        $record,
                        'override',
                        $data['status'],
                        $data['note'],
                        ['payment_status' => $data['payment_status']]
                    );
                }),
            Actions\Action::make('refund')
                ->label('Initiate Refund')
                ->icon('heroicon-o-banknotes')
                ->color('danger')
                ->form([
                    Actions\Components\TextInput::make('amount')->numeric()->required(),
                    Actions\Components\Textarea::make('reason')->rows(3)->required(),
                    Actions\Components\Toggle::make('force')->label('Force after delivery')->default(false),
                ])
                ->action(function (Order $record, array $data): void {
                    $delivered = $record->orderItems()->whereHas('shipments', fn ($q) => $q->whereNotNull('delivered_at'))->exists();
                    if ($delivered && ! $data['force']) {
                        $this->notify('danger', 'Cannot refund after delivery without force.');
                        return;
                    }
                    $record->update(['payment_status' => 'refunded']);
                    OrderAuditLog::create([
                        'order_id' => $record->id,
                        'user_id' => auth()->id(),
                        'action' => 'refund_initiated',
                        'note' => $data['reason'],
                        'payload' => ['amount' => $data['amount'], 'force' => $data['force']],
                    ]);
                    app(EventLogger::class)->order(
                        $record,
                        'refund',
                        'refunded',
                        $data['reason'],
                        ['amount' => $data['amount'], 'force' => $data['force']]
                    );
                }),
        ];
    }
}
