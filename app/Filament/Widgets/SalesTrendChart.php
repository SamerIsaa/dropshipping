<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesTrendChart extends ChartWidget
{
    protected ?string $heading = 'Revenue (30d)';

    protected function getData(): array
    {
        $start = Carbon::now()->subDays(29)->startOfDay();

        $rows = Payment::query()
            ->selectRaw('DATE(paid_at) as day, SUM(amount) as total')
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', $start)
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $labels = [];
        $totals = [];
        $cursor = $start->copy();
        $byDay = $rows->keyBy('day');

        while ($cursor->lte(Carbon::now())) {
            $day = $cursor->toDateString();
            $labels[] = $cursor->format('M d');
            $totals[] = (float) ($byDay[$day]->total ?? 0);
            $cursor->addDay();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $totals,
                    'backgroundColor' => '#2563eb',
                    'borderColor' => '#2563eb',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
