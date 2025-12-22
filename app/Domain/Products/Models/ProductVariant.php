<?php

declare(strict_types=1);

namespace App\Domain\Products\Models;

use App\Domain\Orders\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'cj_vid',
        'sku',
        'title',
        'price',
        'compare_at_price',
        'cost_price',
        'currency',
        'weight_grams',
        'package_length_mm',
        'package_width_mm',
        'package_height_mm',
        'inventory_policy',
        'options',
        'metadata',
    ];

    protected $casts = [
        'options' => 'array',
        'metadata' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplierProducts(): HasMany
    {
        return $this->hasMany(SupplierProduct::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
