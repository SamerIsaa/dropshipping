<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SyncCjProductsJob;
use Illuminate\Console\Command;

class CjSyncProducts extends Command
{
    protected $signature = 'cj:sync-products {--start-page=1} {--pages=1} {--page-size=24} {--queue}';

    protected $description = 'Sync CJ products into local snapshots';

    public function handle(): int
    {
        $start = (int) $this->option('start-page');
        $pages = (int) $this->option('pages');
        $pageSize = (int) $this->option('page-size');
        $queue = (bool) $this->option('queue');

        for ($i = 0; $i < $pages; $i++) {
            $page = $start + $i;
            $job = new SyncCjProductsJob($page, $pageSize);

            if ($queue) {
                dispatch($job);
                $this->info("Queued CJ sync for page {$page}");
            } else {
                dispatch_sync($job);
                $this->info("Synced CJ page {$page}");
            }
        }

        return self::SUCCESS;
    }
}
