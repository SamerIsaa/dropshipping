<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Product;
use App\Models\Supplier;
use App\Policies\ProductPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\OrderPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Product::class => ProductPolicy::class,
        Supplier::class => SupplierPolicy::class,
        \App\Models\Order::class => OrderPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
