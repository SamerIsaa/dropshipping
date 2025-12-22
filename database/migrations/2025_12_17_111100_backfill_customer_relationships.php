<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            UPDATE orders
            INNER JOIN customers ON customers.email = orders.email
            SET orders.customer_id = customers.id
            WHERE orders.customer_id IS NULL
        ');

        DB::statement('
            UPDATE addresses
            INNER JOIN orders ON orders.shipping_address_id = addresses.id
            SET addresses.customer_id = orders.customer_id
            WHERE addresses.customer_id IS NULL AND orders.customer_id IS NOT NULL
        ');

        DB::statement('
            UPDATE addresses
            INNER JOIN orders ON orders.billing_address_id = addresses.id
            SET addresses.customer_id = orders.customer_id
            WHERE addresses.customer_id IS NULL AND orders.customer_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        // No-op for backfill migration.
    }
};
