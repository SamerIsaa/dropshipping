<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function delete(User $user, Product $product): bool
    {
        $hasOrders = $product->variants()
            ->whereHas('orderItems')
            ->exists();

        return ! $hasOrders;
    }

    public function update(User $user, Product $product): bool
    {
        return true;
    }
}
