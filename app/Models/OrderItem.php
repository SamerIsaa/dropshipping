<?php

namespace App\Models;

use App\Domain\Orders\Models\OrderItem as DomainOrderItem;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends DomainOrderItem
{
    public function review(): HasOne
    {
        return $this->hasOne(ProductReview::class);
    }

    public function returnRequest(): HasOne
    {
        return $this->hasOne(ReturnRequest::class);
    }
}
