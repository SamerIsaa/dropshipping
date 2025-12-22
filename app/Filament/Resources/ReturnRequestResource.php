<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReturnRequestResource\Pages;
use App\Models\ReturnRequest;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use App\Filament\Resources\BaseResource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Tables\Filters\SelectFilter;
use UnitEnum;

class ReturnRequestResource extends BaseResource
{
    protected static ?string $model = ReturnRequest::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static UnitEnum|string|null $navigationGroup = 'Operations';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Return request')
                ->schema([
                    Forms\Components\Select::make('order_id')
                        ->relationship('order', 'number')
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('order_item_id')
                        ->relationship('orderItem', 'id')
                        ->searchable(),
                    Forms\Components\Select::make('customer_id')
                        ->relationship('customer', 'email')
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'requested' => 'Requested',
                            'approved' => 'Approved',
                            'received' => 'Received',
                            'rejected' => 'Rejected',
                            'refunded' => 'Refunded',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('reason')->maxLength(120),
                    Forms\Components\Textarea::make('notes')->rows(3),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.number')->label('Order')->searchable(),
                Tables\Columns\TextColumn::make('customer.email')->label('Customer')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('reason')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'requested' => 'Requested',
                    'approved' => 'Approved',
                    'received' => 'Received',
                    'rejected' => 'Rejected',
                    'refunded' => 'Refunded',
                ]),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('viewOrder')
                    ->label('View order')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (ReturnRequest $record) => route('filament.admin.resources.orders.view', $record->order_id))
                    ->openUrlInNewTab(),
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->visible(fn (ReturnRequest $record) => $record->status === 'requested')
                    ->action(fn (ReturnRequest $record) => $record->update(['status' => 'approved'])),
                Action::make('markReceived')
                    ->label('Mark received')
                    ->visible(fn (ReturnRequest $record) => in_array($record->status, ['approved', 'requested'], true))
                    ->action(fn (ReturnRequest $record) => $record->update(['status' => 'received'])),
                Action::make('markRefunded')
                    ->label('Mark refunded')
                    ->color('warning')
                    ->visible(fn (ReturnRequest $record) => in_array($record->status, ['received', 'approved'], true))
                    ->action(fn (ReturnRequest $record) => $record->update(['status' => 'refunded'])),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReturnRequests::route('/'),
            'create' => Pages\CreateReturnRequest::route('/create'),
            'edit' => Pages\EditReturnRequest::route('/{record}/edit'),
        ];
    }
}

