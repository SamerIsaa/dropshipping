<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domain\Fulfillment\Models\FulfillmentProvider;
use App\Domain\Products\Services\PricingService;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
           Section::make('Basics')
                ->schema([
                   TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                   TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->helperText('URL slug')
                        ->dehydrateStateUsing(fn ($state, callable $get) => $state ?: Str::slug($get('name'))),
                   Textarea::make('description')->rows(3),
                   Toggle::make('is_active')->label('Active')->default(true),
                ])->columns(2),
           Section::make('Pricing')
                ->schema([
                   TextInput::make('selling_price')
                        ->label('Selling price')
                        ->numeric()
                        ->required()
                        ->prefix('$')
                        ->rules(function (callable $get) {
                            return [
                                function (string $attribute, $value, callable $fail) use ($get) {
                                    $cost = (float) $get('cost_price');
                                    $selling = (float) $value;
                                    $pricing = PricingService::makeFromConfig();
                                    try {
                                        $pricing->validatePrice($cost, $selling);
                                    } catch (\InvalidArgumentException $e) {
                                        $fail($e->getMessage());
                                    }
                                },
                            ];
                        }),
                   TextInput::make('cost_price')
                        ->label('Cost price')
                        ->numeric()
                        ->required()
                        ->prefix('$')
                        ->afterStateUpdated(function (callable $get, callable $set) {
                            $warning = self::marginWarning($get('selling_price'), $get('cost_price'));
                            $set('margin_warning', $warning);
                        }),
                   Placeholder::make('margin_warning')
                        ->label('Margin warning')
                        ->content(fn (callable $get) => self::marginWarning($get('selling_price'), $get('cost_price')))
                        ->visible(fn (callable $get) => self::marginWarning($get('selling_price'), $get('cost_price')) !== null)
                        ->extraAttributes(['class' => 'text-sm text-amber-600']),
                ])->columns(3),
           Section::make('Suppliers & Fulfillment')
                ->schema([
                   Select::make('supplier_id')
                        ->label('Supplier')
                        ->options(fn () => FulfillmentProvider::query()->where('is_active', true)->pluck('name', 'id'))
                        ->searchable()
                        ->preload(),
                   Select::make('default_fulfillment_provider_id')
                        ->label('Default Fulfillment Provider')
                        ->options(fn () => FulfillmentProvider::query()->where('is_active', true)->pluck('name', 'id'))
                        ->searchable()
                        ->preload(),
                   TextInput::make('supplier_product_url')
                        ->label('Supplier product URL')
                        ->url(),
                   TextInput::make('shipping_estimate_days')
                        ->label('Ship estimate (days)')
                        ->numeric()
                        ->minValue(0),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('selling_price')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('cost_price')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')->label('Supplier')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('defaultFulfillmentProvider.name')
                    ->label('Fulfillment')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('shipping_estimate_days')
                    ->label('Ship est. (d)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->recordActions([
                EditAction::make(),
                // Actions\Action::make('toggleActive')
                //     ->label('Activate/Deactivate')
                //     ->icon('heroicon-o-power')
                //     ->action(fn (Product $record) => $record->update(['is_active' => ! $record->is_active])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    private static function marginWarning($selling, $cost): ?string
    {
        if (! $selling || ! $cost) {
            return null;
        }

        $pricing = PricingService::makeFromConfig();
        $min = $pricing->minSellingPrice((float) $cost);

        return $selling < $min
            ? "Warning: selling price is below required margin (min {$min})."
            : null;
    }
}
