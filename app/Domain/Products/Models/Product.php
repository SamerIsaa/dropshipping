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
        'cj_pid',
        'cj_sync_enabled',
        'cj_synced_at',
        'cj_last_payload',
        'cj_last_changed_fields',
        'cj_lock_price',
        'cj_lock_description',
        'cj_lock_images',
        'cj_lock_variants',
        'cj_video_urls',
        'stock_on_hand',
        'slug',
        'name',
        'category_id',
        'description',
        'meta_title',
        'meta_description',
        'selling_price',
        'cost_price',
        'status',
        'currency',
        'default_fulfillment_provider_id',
        'supplier_id',
        'supplier_product_url',
        'shipping_estimate_days',
        'is_active',
        'is_featured',
        'source_url',
        'options',
        'attributes',
    ];

    protected $casts = [
        'options' => 'array',
        'attributes' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'cj_sync_enabled' => 'boolean',
        'cj_synced_at' => 'datetime',
        'cj_last_payload' => 'array',
        'cj_last_changed_fields' => 'array',
        'cj_lock_price' => 'boolean',
        'cj_lock_description' => 'boolean',
        'cj_lock_images' => 'boolean',
        'cj_lock_variants' => 'boolean',
        'cj_video_urls' => 'array',
        'stock_on_hand' => 'integer',
    ];

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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

    public function reviews(): HasMany
    {
        return $this->hasMany(\App\Models\ProductReview::class);
    }
}
