<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Orders\Models\OrderItem;
use App\Filament\Resources\ProductResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class TopSellersTable extends BaseWidget
{
    protected static ?string $heading = 'Top Sellers';
    protected int|string|array $columnSpan = 'full';
    protected ?string $defaultSortColumn = 'units';
    protected ?string $defaultSortDirection = 'desc';

    public function table(Table $table): Table
    {
        return $table->defaultKeySort(false);
    }

    protected function getTableQuery(): Builder|Relation|null
    {
        return OrderItem::query()
            ->selectRaw('product_variant_id, SUM(quantity) as units, SUM(total) as revenue')
            ->whereNotNull('product_variant_id')
            ->groupBy('product_variant_id')
            ->with('productVariant.product')
            ->orderByDesc('units');
    }

    public function getTableRecordKey($record): string
    {
        if (! empty($record->product_variant_id)) {
            return (string) $record->product_variant_id;
        }

        return md5(json_encode([
            $record->productVariant?->product_id,
            $record->units,
            $record->revenue,
        ]));
    }

    protected function getTableRecordUrl($record): ?string
    {
        $productId = $record->productVariant?->product_id;

        return $productId
            ? ProductResource::getUrl('edit', ['record' => $productId])
            : null;
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('productVariant.product.name')->label('Product')->limit(30),
            Tables\Columns\TextColumn::make('productVariant.title')->label('Variant')->limit(30),
            Tables\Columns\TextColumn::make('units')->label('Units'),
            Tables\Columns\TextColumn::make('revenue')->label('Revenue')->money('USD'),
        ];
    }
}
