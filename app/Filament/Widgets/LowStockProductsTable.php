<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Products\Models\Product;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class LowStockProductsTable extends BaseWidget
{
    protected static ?string $heading = 'Low Stock Products';
    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): Builder|Relation|null
    {
        return Product::query()
            ->where('is_active', true)
            ->whereNotNull('stock_on_hand')
            ->where('stock_on_hand', '<=', 5)
            ->orderBy('stock_on_hand');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')->label('Product')->searchable()->limit(40),
            Tables\Columns\TextColumn::make('stock_on_hand')->label('Stock')->sortable(),
            Tables\Columns\TextColumn::make('updated_at')->since()->label('Updated'),
        ];
    }
}
