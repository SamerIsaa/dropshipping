<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\ProductReview;
use Illuminate\Support\Facades\Schedule;
use App\Infrastructure\Fulfillment\Clients\CJDropshippingClient;
use App\Domain\Products\Services\CjProductImportService;
use App\Models\SiteSetting;
use App\Jobs\PollCJFulfillmentStatus;
use App\Domain\Fulfillment\Models\FulfillmentJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('data:cleanup-customers {--dry-run}', function () {
    $dryRun = (bool) $this->option('dry-run');

    $this->info($dryRun ? 'Running in dry-run mode.' : 'Running cleanup.');

    $duplicates = DB::table('customers')
        ->select('email', DB::raw('COUNT(*) as count'))
        ->whereNotNull('email')
        ->groupBy('email')
        ->having('count', '>', 1)
        ->get();

    foreach ($duplicates as $dup) {
        $ids = DB::table('customers')->where('email', $dup->email)->orderBy('id')->pluck('id')->all();
        $keepId = array_shift($ids);

        if (! $dryRun) {
            DB::table('orders')->whereIn('customer_id', $ids)->update(['customer_id' => $keepId]);
            DB::table('addresses')->whereIn('customer_id', $ids)->update(['customer_id' => $keepId]);
            DB::table('payment_methods')->whereIn('customer_id', $ids)->update(['customer_id' => $keepId]);
            DB::table('gift_cards')->whereIn('customer_id', $ids)->update(['customer_id' => $keepId]);
            DB::table('coupon_redemptions')->whereIn('customer_id', $ids)->update(['customer_id' => $keepId]);
            DB::table('customers')->whereIn('id', $ids)->delete();
        }

        $this->line("Merged duplicates for {$dup->email}: kept {$keepId}, removed " . implode(',', $ids));
    }

    $orders = DB::table('orders')
        ->whereNull('customer_id')
        ->whereNotNull('email')
        ->get();

    foreach ($orders as $order) {
        $customer = DB::table('customers')->where('email', $order->email)->first();

        if (! $customer && ! $dryRun) {
            $shipping = $order->shipping_address_id
                ? DB::table('addresses')->where('id', $order->shipping_address_id)->first()
                : null;

            $name = $shipping?->name ?: $order->email;
            $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
            $first = array_shift($parts) ?: $order->email;
            $last = $parts ? implode(' ', $parts) : null;

            $customerId = DB::table('customers')->insertGetId([
                'first_name' => $first,
                'last_name' => $last,
                'email' => $order->email,
                'phone' => $shipping?->phone,
                'country_code' => $shipping?->country,
                'city' => $shipping?->city,
                'region' => $shipping?->state,
                'address_line1' => $shipping?->line1,
                'address_line2' => $shipping?->line2,
                'postal_code' => $shipping?->postal_code,
                'metadata' => json_encode(['source' => 'cleanup']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $customerId = $customer?->id;
        }

        if ($customerId && ! $dryRun) {
            DB::table('orders')->where('id', $order->id)->update(['customer_id' => $customerId]);

            DB::table('addresses')
                ->whereIn('id', array_filter([$order->shipping_address_id, $order->billing_address_id]))
                ->whereNull('customer_id')
                ->update(['customer_id' => $customerId]);
        }
    }

    $this->info('Cleanup complete.');
})->purpose('Deduplicate customers and backfill customer relationships.');

Artisan::command('reviews:auto-approve', function () {
    $settings = SiteSetting::query()->first();
    $days = (int) ($settings?->auto_approve_review_days ?? 0);

    if ($days <= 0) {
        $this->info('Auto-approve is disabled.');
        return;
    }

    $cutoff = now()->subDays($days);
    $count = ProductReview::query()
        ->where('status', 'pending')
        ->where('created_at', '<=', $cutoff)
        ->update(['status' => 'approved']);

    $this->info("Approved {$count} review(s).");
})->purpose('Approve pending reviews older than the configured number of days.');

Schedule::command('reviews:auto-approve')->daily();

Artisan::command('cj:token', function () {
    $client = app(CJDropshippingClient::class);
    $token = $client->getAccessToken(true);
    $this->info('CJ access token: ' . $token);
})->purpose('Fetch and cache a CJ Dropshipping access token');

Artisan::command('cj:settings', function () {
    $client = app(CJDropshippingClient::class);
    $resp = $client->getSettings();
    $this->info('CJ settings:');
    $this->line(json_encode($resp->data, JSON_PRETTY_PRINT));
})->purpose('Fetch CJ account settings');

Artisan::command('cj:logout', function () {
    $client = app(CJDropshippingClient::class);
    $resp = $client->logout();
    $this->info($resp->ok ? 'CJ tokens cleared and logout requested.' : 'Logout call failed.');
})->purpose('Logout CJ access token and clear cache');

Artisan::command('cj:set-account {--name=} {--email=}', function () {
    $name = $this->option('name');
    $email = $this->option('email');

    if (! $name && ! $email) {
        $this->error('Provide --name and/or --email');
        return;
    }

    $client = app(CJDropshippingClient::class);
    $resp = $client->updateAccount($name, $email);
    $this->info('CJ account updated: ' . ($resp->message ?? 'OK'));
    $this->line(json_encode($resp->data, JSON_PRETTY_PRINT));
})->purpose('Update CJ account openName/openEmail');

Artisan::command('cj:product {pid}', function (string $pid) {
    $client = app(CJDropshippingClient::class);
    $resp = $client->getProduct($pid);
    $this->info('Product:');
    $this->line(json_encode($resp->data, JSON_PRETTY_PRINT));
})->purpose('Fetch CJ product details by pid');

Artisan::command('cj:variants {pid}', function (string $pid) {
    $client = app(CJDropshippingClient::class);
    $resp = $client->getVariantsByPid($pid);
    $this->info('Variants:');
    $this->line(json_encode($resp->data, JSON_PRETTY_PRINT));
})->purpose('Fetch CJ variants by pid');

Artisan::command('cj:variant-stock {vid}', function (string $vid) {
    $client = app(CJDropshippingClient::class);
    $resp = $client->getStockByVid($vid);
    $this->info('Stock:');
    $this->line(json_encode($resp->data, JSON_PRETTY_PRINT));
})->purpose('Fetch CJ stock by variant vid');

Artisan::command('cj:sync-products {--start-page=1} {--pages=1} {--page-size=24} {--queue}', function () {
    $start = (int) $this->option('start-page');
    $pages = (int) $this->option('pages');
    $pageSize = (int) $this->option('page-size');
    $queue = (bool) $this->option('queue');

    for ($i = 0; $i < $pages; $i++) {
        $page = $start + $i;
        $job = new \App\Jobs\SyncCjProductsJob($page, $pageSize);

        if ($queue) {
            dispatch($job);
            $this->info("Queued CJ sync for page {$page}");
        } else {
            dispatch_sync($job);
            $this->info("Synced CJ page {$page}");
        }
    }
})->purpose('Sync CJ products into local snapshots');

Artisan::command('cj:sync-my-products {--start-page=1} {--page-size=24} {--max-pages=50} {--force-update}', function () {
    $start = (int) $this->option('start-page');
    $pageSize = (int) $this->option('page-size');
    $maxPages = (int) $this->option('max-pages');
    $forceUpdate = (bool) $this->option('force-update');

    $importer = app(CjProductImportService::class);

    $startedAt = microtime(true);

    try {
        $summary = $importer->syncMyProducts($start, $pageSize, $maxPages, $forceUpdate);
    } catch (\App\Services\Api\ApiException $e) {
        $this->error("CJ sync failed: {$e->getMessage()}");
        return;
    }

    $duration = microtime(true) - $startedAt;
    $message = sprintf(
        'Synced %d product(s) (processed %d, errors %d) in %.2fs.',
        $summary['imported'],
        $summary['processed'],
        $summary['errors'],
        $duration
    );

    $this->info($message);

    $settings = SiteSetting::query()->first();
    if (! $settings) {
        $settings = SiteSetting::create([]);
    }

    $settings->update([
        'cj_last_sync_at' => now(),
        'cj_last_sync_summary' => $message,
    ]);
})->purpose('Sync CJ My Products into the local catalog');

Schedule::command('cj:sync-my-products')->hourly()->name('cj:sync-my-products');

Artisan::command('cj:import-snapshots {--limit=200}', function () {
    $limit = (int) $this->option('limit');
    $this->call(\App\Console\Commands\CjImportSnapshots::class, ['--limit' => $limit]);
})->purpose('Import CJ snapshots into Category/Product tables');

Schedule::call(function () {
    $cjJobs = FulfillmentJob::query()
        ->whereNull('fulfilled_at')
        ->where('status', '!=', 'failed')
        ->whereHas('provider', fn ($q) => $q->where('driver_class', \App\Domain\Fulfillment\Strategies\CJDropshippingFulfillmentStrategy::class))
        ->limit(50)
        ->pluck('id');

    foreach ($cjJobs as $jobId) {
        dispatch(new PollCJFulfillmentStatus($jobId))->onQueue('default');
    }
})->hourly()->name('cj:poll-fulfillment');
