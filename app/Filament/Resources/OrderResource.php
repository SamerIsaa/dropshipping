<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms;
use App\Filament\Resources\OrderResource\RelationManagers\OrderEventsRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\PaymentEventsRelationManager;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')->label('Order #')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('shippingAddress.name')->label('Customer')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('status')->label('Fulfillment')->badge()->sortable(),
                Tables\Columns\TextColumn::make('payment_status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('grand_total')->money(fn (Order $record) => $record->currency)->sortable(),
                Tables\Columns\TextColumn::make('placed_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')->options([
                    'unpaid' => 'Unpaid',
                    'paid' => 'Paid',
                    'refunded' => 'Refunded',
                ]),
                Tables\Filters\SelectFilter::make('status')->label('Fulfillment status')->options([
                    'pending' => 'Pending',
                    'paid' => 'Paid',
                    'fulfilling' => 'Fulfilling',
                    'fulfilled' => 'Fulfilled',
                    'cancelled' => 'Cancelled',
                    'refunded' => 'Refunded',
                ]),
                Tables\Filters\SelectFilter::make('fulfillment_provider')
                    ->label('Fulfillment provider')
                    ->relationship('orderItems.fulfillmentProvider', 'name'),
                Tables\Filters\Filter::make('placed_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('placed_at', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('placed_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
              BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Order')
                ->schema([
                    TextEntry::make('number')->label('Order #')->copyable(),
                    TextEntry::make('status')->badge(),
                    TextEntry::make('payment_status')->badge(),
                    TextEntry::make('grand_total')->money(fn ($record) => $record->currency),
                    TextEntry::make('placed_at')->dateTime(),
                ])->columns(5),
            Section::make('Customer')
                ->schema([
                    TextEntry::make('email')->copyable(),
                    TextEntry::make('shippingAddress.name')->label('Name'),
                    TextEntry::make('shippingAddress.phone')->label('Phone')->copyable(),
                    TextEntry::make('shippingAddress.line1')->label('Address')->copyable(),
                    TextEntry::make('shippingAddress.city')->label('City')->copyable(),
                    TextEntry::make('shippingAddress.country')->label('Country'),
                ])->columns(2),
            Section::make('Shipping')
                ->schema([
                    TextEntry::make('shippingAddress.name')->label('Name')->copyable(),
                    TextEntry::make('shippingAddress.phone')->label('Phone')->copyable(),
                    TextEntry::make('shippingAddress.line1')->label('Line 1')->copyable(),
                    TextEntry::make('shippingAddress.line2')->label('Line 2')->copyable(),
                    TextEntry::make('shippingAddress.city')->label('City')->copyable(),
                    TextEntry::make('shippingAddress.state')->label('State')->copyable(),
                    TextEntry::make('shippingAddress.postal_code')->label('Postal')->copyable(),
                    TextEntry::make('shippingAddress.country')->label('Country'),
                ])->columns(4),
            Section::make('Billing')
                ->schema([
                    TextEntry::make('billingAddress.name')->label('Name')->copyable(),
                    TextEntry::make('billingAddress.line1')->label('Line 1')->copyable(),
                    TextEntry::make('billingAddress.city')->label('City'),
                    TextEntry::make('billingAddress.country')->label('Country'),
                ])->columns(4),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            OrderItemsRelationManager::class,
            OrderEventsRelationManager::class,
            PaymentEventsRelationManager::class,
            \App\Filament\Resources\OrderResource\RelationManagers\FulfillmentEventsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
