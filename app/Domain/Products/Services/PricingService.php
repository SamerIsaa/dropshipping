<?php

declare(strict_types=1);

namespace App\Domain\Products\Services;

use InvalidArgumentException;

class PricingService
{
    public function __construct(
        private readonly float $minMarginPercent = 0,
        private readonly float $shippingBufferPercent = 0,
        private readonly float $maxDiscountPercent = 0,
    ) {
    }

    public static function makeFromConfig(): self
    {
        return new self(
            minMarginPercent: (float) config('pricing.min_margin_percent', 20),
            shippingBufferPercent: (float) config('pricing.shipping_buffer_percent', 10),
            maxDiscountPercent: (float) config('pricing.max_discount_percent', 30),
        );
    }

    /**
     * Calculate the minimum allowed selling price based on cost, margin, and shipping buffer.
     */
    public function minSellingPrice(float $cost): float
    {
        if ($cost < 0) {
            throw new InvalidArgumentException('Cost price cannot be negative.');
        }

        $bufferedCost = $cost * (1 + $this->shippingBufferPercent / 100);

        return round($bufferedCost * (1 + $this->minMarginPercent / 100), 2);
    }

    /**
     * Validate a selling price against rules.
     */
    public function validatePrice(float $cost, float $selling): void
    {
        $min = $this->minSellingPrice($cost);

        if ($selling < $min) {
            throw new InvalidArgumentException("Selling price must be at least {$min} based on margin rules.");
        }

        if ($selling < $cost) {
            throw new InvalidArgumentException('Selling price cannot be below cost.');
        }
    }

    /**
     * Validate discount against max discount percent (applied on current price).
     */
    public function validateDiscount(float $price, float $discountAmount): void
    {
        if ($price <= 0) {
            throw new InvalidArgumentException('Price must be positive for discount validation.');
        }

        $discountPercent = ($discountAmount / $price) * 100;

        if ($discountPercent > $this->maxDiscountPercent) {
            throw new InvalidArgumentException("Discount exceeds max allowed {$this->maxDiscountPercent}%.");
        }
    }
}
