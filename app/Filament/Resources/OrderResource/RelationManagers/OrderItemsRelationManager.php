<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Domain\Fulfillment\Models\FulfillmentProvider;
use App\Domain\Orders\Services\TrackingService;
use App\Domain\Orders\Models\OrderAuditLog;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('productVariant.title')->label('Variant'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('unit_price')->money(fn ($record) => $record->order->currency),
                Tables\Columns\TextColumn::make('fulfillment_status')->badge(),
                Tables\Columns\SelectColumn::make('fulfillment_provider_id')
                    ->label('Fulfillment Provider')
                    ->options(fn () => FulfillmentProvider::query()->where('is_active', true)->pluck('name', 'id'))
                    ->selectablePlaceholder(false),
                Tables\Columns\TextColumn::make('supplierProduct.external_product_id')
                    ->label('Supplier Link')
                    ->url(fn ($record) => $record->supplierProduct?->external_product_id
                        ? 'https://www.aliexpress.com/item/'.$record->supplierProduct->external_product_id.'.html'
                        : null, true),
                Tables\Columns\TextColumn::make('tracking_number_display')
                    ->label('Tracking')
                    ->getStateUsing(fn ($record) => $record->shipments()->latest('shipped_at')->value('tracking_number'))
                    ->tooltip(fn ($record) => $record->shipments()->latest('shipped_at')->value('tracking_url'))
                    ->copyable(),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('addTracking')
                    ->label('Add Tracking')
                    ->icon('heroicon-o-truck')
                    ->form([
                        Forms\Components\TextInput::make('tracking_number')->required(),
                        Forms\Components\TextInput::make('carrier'),
                        Forms\Components\TextInput::make('tracking_url')->url(),
                        Forms\Components\DateTimePicker::make('shipped_at')->default(now()),
                    ])
                    ->action(function ($record, array $data): void {
                        $tracking = app(TrackingService::class);
                        $tracking->recordShipment($record, [
                            'tracking_number' => $data['tracking_number'],
                            'carrier' => $data['carrier'] ?? null,
                            'tracking_url' => $data['tracking_url'] ?? null,
                            'shipped_at' => $data['shipped_at'] ?? now(),
                        ]);

                        if ($record->fulfillment_status !== 'fulfilled') {
                            $record->update(['fulfillment_status' => 'fulfilled']);
                        }
                        OrderAuditLog::create([
                            'order_id' => $record->order_id,
                            'user_id' => auth()->id(),
                            'action' => 'tracking_added',
                            'note' => 'Tracking number added',
                            'payload' => $data,
                        ]);
                    }),
                Tables\Actions\Action::make('addTrackingEvent')
                    ->label('Add Tracking Event')
                    ->icon('heroicon-o-clock')
                    ->form([
                        Forms\Components\TextInput::make('tracking_number')
                            ->label('Tracking number')
                            ->required(),
                        Forms\Components\TextInput::make('status_code')->required(),
                        Forms\Components\TextInput::make('status_label'),
                        Forms\Components\TextInput::make('location'),
                        Forms\Components\DateTimePicker::make('occurred_at')->default(now())->required(),
                        Forms\Components\Textarea::make('description')->rows(2),
                    ])
                    ->action(function ($record, array $data): void {
                        $tracking = app(TrackingService::class);
                        $shipment = $record->shipments()
                            ->where('tracking_number', $data['tracking_number'])
                            ->first();

                        if (! $shipment) {
                            $shipment = $tracking->recordShipment($record, [
                                'tracking_number' => $data['tracking_number'],
                                'carrier' => null,
                                'shipped_at' => now(),
                            ]);
                        }

                        $tracking->recordEvent($shipment, [
                            'status_code' => $data['status_code'],
                            'status_label' => $data['status_label'] ?? null,
                            'description' => $data['description'] ?? null,
                            'location' => $data['location'] ?? null,
                            'occurred_at' => $data['occurred_at'],
                        ]);
                        OrderAuditLog::create([
                            'order_id' => $record->order_id,
                            'user_id' => auth()->id(),
                            'action' => 'tracking_event_added',
                            'note' => $data['description'] ?? 'Tracking event added',
                            'payload' => $data,
                        ]);
                    }),
                Tables\Actions\Action::make('overrideStatus')
                    ->label('Override Status')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('fulfillment_status')->options([
                            'pending' => 'Pending',
                            'awaiting_fulfillment' => 'Awaiting',
                            'fulfilling' => 'Ordered',
                            'fulfilled' => 'Delivered',
                            'failed' => 'Failed',
                        ])->required(),
                        Forms\Components\Textarea::make('note')->rows(2)->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update(['fulfillment_status' => $data['fulfillment_status']]);
                        OrderAuditLog::create([
                            'order_id' => $record->order_id,
                            'user_id' => auth()->id(),
                            'action' => 'fulfillment_override',
                            'note' => $data['note'],
                            'payload' => ['status' => $data['fulfillment_status']],
                        ]);
                    }),
                Tables\Actions\Action::make('updateStatus')
                    ->label('Update Status')
                    ->icon('heroicon-o-check-circle')
                    ->form([
                        Forms\Components\Select::make('fulfillment_status')->options([
                            'pending' => 'Pending',
                            'awaiting_fulfillment' => 'Awaiting',
                            'fulfilling' => 'Ordered',
                            'fulfilled' => 'Delivered',
                            'failed' => 'Failed',
                        ])->required(),
                        Forms\Components\Textarea::make('note')->rows(2)
                            ->required(fn ($get) => in_array($get('fulfillment_status'), ['failed'], true)),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update(['fulfillment_status' => $data['fulfillment_status']]);
                        OrderAuditLog::create([
                            'order_id' => $record->order_id,
                            'user_id' => auth()->id(),
                            'action' => 'fulfillment_status_updated',
                            'note' => $data['note'] ?? null,
                            'payload' => ['status' => $data['fulfillment_status']],
                        ]);
                    }),
            ])
            ->bulkActions([]);
    }
}
