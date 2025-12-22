<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::updateOrCreate([], [
            'site_name' => 'Dispatch Store',
            'site_description' => 'Curated essentials delivered fast.',
            'meta_title' => 'Dispatch Store',
            'meta_description' => 'Shop curated essentials with reliable shipping and easy returns.',
            'meta_keywords' => 'dropshipping, store, ecommerce, deals',
            'logo_path' => null,
            'favicon_path' => null,
            'timezone' => config('app.timezone', 'UTC'),
            'primary_color' => '#0f172a',
            'secondary_color' => '#f97316',
            'accent_color' => '#22c55e',
            'support_email' => 'support@dispatch.store',
            'support_whatsapp' => '+22500000000',
            'support_phone' => '+22500000000',
            'delivery_window' => '7-18 business days',
            'shipping_message' => 'Standard tracked delivery to Cote dIvoire.',
            'customs_message' => 'Duties and VAT are disclosed before payment when available.',
            'tax_label' => 'VAT',
            'tax_rate' => 0,
            'tax_included' => false,
            'shipping_handling_fee' => 0,
            'free_shipping_threshold' => null,
            'auto_approve_reviews' => false,
            'auto_approve_review_days' => 0,
        ]);
    }
}
