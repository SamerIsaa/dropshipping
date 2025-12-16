<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class QueueServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Queue::failing(function (JobFailed $event) {
            Log::error('Queue job failed', [
                'job' => $event->job?->resolveName(),
                'queue' => $event->job?->getQueue(),
                'exception' => $event->exception->getMessage(),
                'trace' => $event->exception->getTraceAsString(),
            ]);
        });
    }
}
