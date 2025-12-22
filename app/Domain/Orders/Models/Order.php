<?php

declare(strict_types=1);

namespace App\Domain\Orders\Models;

use App\Domain\Common\Models\Address;
use App\Domain\Payments\Models\Payment;
use App\Domain\Orders\Models\OrderEvent;
use App\Domain\Orders\Models\OrderItem;
use App\Domain\Orders\Models\OrderAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'user_id',
        'customer_id',
        'email',
        'status',
        'payment_status',
        'currency',
        'subtotal',
        'shipping_total',
        'tax_total',
        'discount_total',
        'grand_total',
        'shipping_address_id',
        'billing_address_id',
        'shipping_method',
        'delivery_notes',
        'coupon_code',
        'placed_at',
    ];

    protected $casts = [
        'placed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(OrderAuditLog::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class);
    }

    public function paymentEvents(): HasManyThrough
    {
        return $this->hasManyThrough(\App\Domain\Payments\Models\PaymentEvent::class, Payment::class);
    }

    public function fulfillmentEvents(): HasManyThrough
    {
        return $this->hasManyThrough(\App\Domain\Fulfillment\Models\FulfillmentEvent::class, OrderItem::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }
}
