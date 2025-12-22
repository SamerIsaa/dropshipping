<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\CjSyncCatalog;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CjSyncCatalog::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('cj:sync-catalog')->dailyAt('02:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
