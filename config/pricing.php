<?php

return [
    // Minimum margin buffer over cost (in percent) required for selling price
    'min_margin_percent' => env('PRICING_MIN_MARGIN_PERCENT', 20),

    // Shipping cost buffer applied on top of cost (in percent)
    'shipping_buffer_percent' => env('PRICING_SHIPPING_BUFFER_PERCENT', 10),

    // Maximum discount percent allowed from the computed target price
    'max_discount_percent' => env('PRICING_MAX_DISCOUNT_PERCENT', 30),
];
