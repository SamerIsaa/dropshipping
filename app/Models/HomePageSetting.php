<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomePageSetting extends Model
{
    protected $fillable = [
        'top_strip',
        'hero_slides',
        'rail_cards',
        'banner_strip',
    ];

    protected $casts = [
        'top_strip' => 'array',
        'hero_slides' => 'array',
        'rail_cards' => 'array',
        'banner_strip' => 'array',
    ];
}
