<?php

declare(strict_types=1);

namespace App\Domain\Products\Models;

use App\Domain\Fulfillment\Models\FulfillmentProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'selling_price',
        'cost_price',
        'status',
        'currency',
        'default_fulfillment_provider_id',
        'supplier_id',
        'supplier_product_url',
        'shipping_estimate_days',
        'is_active',
        'source_url',
        'options',
        'attributes',
    ];

    protected $casts = [
        'options' => 'array',
        'attributes' => 'array',
        'is_active' => 'boolean',
    ];

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function defaultFulfillmentProvider(): BelongsTo
    {
        return $this->belongsTo(FulfillmentProvider::class, 'default_fulfillment_provider_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(FulfillmentProvider::class, 'supplier_id');
    }
}
