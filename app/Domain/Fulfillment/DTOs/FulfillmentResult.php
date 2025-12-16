<?php

declare(strict_types=1);

namespace App\Domain\Fulfillment\DTOs;

class FulfillmentResult
{
    public function __construct(
        public string $status,
        public ?string $externalReference = null,
        public ?string $trackingNumber = null,
        public ?string $trackingUrl = null,
        public array $rawResponse = [],
    ) {
    }

    public function succeeded(): bool
    {
        return $this->status === 'succeeded';
    }

    public function failed(): bool
    {
        return $this->status === 'failed';
    }
}
