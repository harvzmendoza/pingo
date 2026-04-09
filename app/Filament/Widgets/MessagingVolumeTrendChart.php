<?php

namespace App\Filament\Widgets;

use App\Models\MessageLog;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class MessagingVolumeTrendChart extends ChartWidget
{
    protected ?string $heading = 'Messaging Volume (Last 14 Days)';

    protected ?string $pollingInterval = '30s';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $dates = collect(range(13, 0))
            ->map(fn (int $daysAgo): CarbonImmutable => CarbonImmutable::today()->subDays($daysAgo));

        $rows = MessageLog::query()
            ->selectRaw('DATE(sent_at) as sent_date, COUNT(*) as total')
            ->whereDate('sent_at', '>=', CarbonImmutable::today()->subDays(13))
            ->groupBy('sent_date')
            ->get();

        $series = $dates->map(function (CarbonImmutable $date) use ($rows): int {
            return (int) ($rows
                ->first(fn (MessageLog $row): bool => $row->sent_date === $date->toDateString())
                ->total ?? 0);
        });

        return [
            'datasets' => [[
                'label' => 'Delivery Attempts',
                'data' => $series->all(),
                'borderColor' => '#7C3AED',
                'backgroundColor' => 'rgba(124, 58, 237, 0.15)',
                'tension' => 0.35,
            ]],
            'labels' => $dates->map(fn (CarbonImmutable $date): string => $date->format('M d'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
