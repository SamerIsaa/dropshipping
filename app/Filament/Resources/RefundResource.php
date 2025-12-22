<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RefundResource\Pages;
use App\Filament\Resources\PaymentResource;
use App\Models\Payment;
use BackedEnum;
use Filament\Actions\ViewAction;
use App\Filament\Resources\BaseResource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RefundResource extends BaseResource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-uturn-left';
    protected static string|\UnitEnum|null $navigationGroup = 'Sales';
    protected static ?int $navigationSort = 20;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.number')->label('Order #')->searchable(),
                Tables\Columns\TextColumn::make('provider')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('amount')->money(fn ($record) => $record->currency),
                Tables\Columns\TextColumn::make('provider_reference')->label('Reference')->limit(20),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->label('Updated'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => PaymentResource::getUrl('view', ['record' => $record])),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRefunds::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', 'refunded');
    }

    public static function canCreate(): bool
    {
        return false;
    }
}



