<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers\PaymentEventsRelationManager;
use App\Filament\Resources\PaymentResource\RelationManagers\PaymentWebhooksRelationManager;
use App\Models\Payment;
use BackedEnum;
use Filament\Actions\ViewAction;
use App\Filament\Resources\BaseResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentResource extends BaseResource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static string|\UnitEnum|null $navigationGroup = 'Payments';
    protected static ?int $navigationSort = 10;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.number')->label('Order #')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('provider')->badge()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('amount')->money(fn ($record) => $record->currency)->sortable(),
                Tables\Columns\TextColumn::make('provider_reference')->label('Reference')->limit(20)->toggleable(),
                Tables\Columns\TextColumn::make('paid_at')->dateTime()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'authorized' => 'Authorized',
                    'paid' => 'Paid',
                    'failed' => 'Failed',
                    'refunded' => 'Refunded',
                ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Payment')
                ->schema([
                    TextEntry::make('order.number')->label('Order #'),
                    TextEntry::make('provider')->badge(),
                    TextEntry::make('status')->badge(),
                    TextEntry::make('provider_reference')->label('Reference'),
                    TextEntry::make('amount')->money(fn ($record) => $record->currency),
                    TextEntry::make('paid_at')->dateTime(),
                    TextEntry::make('created_at')->dateTime(),
                ])->columns(3),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            PaymentEventsRelationManager::class,
            PaymentWebhooksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}



