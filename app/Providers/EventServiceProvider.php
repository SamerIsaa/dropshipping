<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\Orders\OrderPlaced;
use App\Events\Orders\OrderPaid;
use App\Events\Orders\FulfillmentDelayed;
use App\Events\Orders\CustomsUpdated;
use App\Events\Orders\OrderDelivered;
use App\Events\Orders\RefundProcessed;
use App\Listeners\Orders\SendOrderConfirmedNotification;
use App\Listeners\Orders\SendShippingDelayNotification;
use App\Listeners\Orders\SendCustomsInfoNotification;
use App\Listeners\Orders\SendDeliveryConfirmedNotification;
use App\Listeners\Orders\SendRefundProcessedNotification;
use App\Listeners\Auth\LogAdminLogin;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Login::class => [
            LogAdminLogin::class,
        ],
        OrderPlaced::class => [
            SendOrderConfirmedNotification::class,
        ],
        OrderPaid::class => [
            SendOrderConfirmedNotification::class,
        ],
        FulfillmentDelayed::class => [
            SendShippingDelayNotification::class,
        ],
        CustomsUpdated::class => [
            SendCustomsInfoNotification::class,
        ],
        OrderDelivered::class => [
            SendDeliveryConfirmedNotification::class,
        ],
        RefundProcessed::class => [
            SendRefundProcessedNotification::class,
        ],
    ];
}
