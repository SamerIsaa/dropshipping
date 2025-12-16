<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fulfillment_providers', function (Blueprint $table) {
            $table->string('type')->default('aliexpress')->after('name');
            $table->json('contact_info')->nullable()->after('driver_class');
            $table->text('notes')->nullable()->after('contact_info');
            $table->boolean('is_blacklisted')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('fulfillment_providers', function (Blueprint $table) {
            $table->dropColumn(['type', 'contact_info', 'notes', 'is_blacklisted']);
        });
    }
};
