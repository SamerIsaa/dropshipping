<?php

declare(strict_types=1);

namespace App\Infrastructure\Fulfillment\Clients\CJ;

use App\Infrastructure\Fulfillment\Clients\CJDropshippingClient;
use App\Services\Api\ApiClient;

abstract class CjBaseApi
{
    public function __construct(protected CJDropshippingClient $root)
    {
    }

    protected function client(): ApiClient
    {
        return $this->root->authClient();
    }
}
