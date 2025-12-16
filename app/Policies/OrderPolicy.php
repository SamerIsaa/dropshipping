<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function delete(User $user, Order $order): bool
    {
        // Prevent destructive delete
        return false;
    }

    public function update(User $user, Order $order): bool
    {
        return true;
    }
}
