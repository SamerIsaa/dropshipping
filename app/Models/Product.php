<?php

namespace App\Models;

use App\Domain\Products\Models\Product as DomainProduct;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends DomainProduct
{
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }
}
