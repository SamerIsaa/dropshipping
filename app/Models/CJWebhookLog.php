<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CJWebhookLog extends Model
{
    protected $fillable = [
        'message_id',
        'type',
        'message_type',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
