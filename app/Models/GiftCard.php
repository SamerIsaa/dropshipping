<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'code',
        'balance',
        'currency',
        'status',
        'expires_at',
        'redeemed_at',
        'meta',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'expires_at' => 'datetime',
        'redeemed_at' => 'datetime',
        'meta' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
