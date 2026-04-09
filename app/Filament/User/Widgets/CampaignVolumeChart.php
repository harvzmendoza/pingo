<?php

namespace App\Filament\User\Widgets;

use App\Models\Message;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class CampaignVolumeChart extends ChartWidget
{
    protected ?string $heading = 'Campaigns Created (Last 7 Days)';

    protected ?string $pollingInterval = '30s';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $userId = Filament::auth()->id();

        $dates = collect(range(6, 0))
            ->map(fn (int $daysAgo): CarbonImmutable => CarbonImmutable::today()->subDays($daysAgo));

        if (! $userId) {
            return [
                'datasets' => [
                    ['label' => 'Campaigns', 'data' => array_fill(0, 7, 0)],
                ],
                'labels' => $dates->map(fn (CarbonImmutable $date): string => $date->format('M d'))->all(),
            ];
        }

        $rows = Message::query()
            ->selectRaw('DATE(created_at) as created_date, COUNT(*) as total')
            ->where('user_id', $userId)
            ->whereDate('created_at', '>=', CarbonImmutable::today()->subDays(6))
            ->groupBy('created_date')
            ->get()
            ->keyBy('created_date');

        $series = $dates->map(
            fn (CarbonImmutable $date): int => (int) ($rows->get($date->toDateString())->total ?? 0)
        );

        return [
            'datasets' => [
                [
                    'label' => 'Campaigns',
                    'data' => $series->all(),
                    'backgroundColor' => 'rgba(124, 58, 237, 0.65)',
                    'borderRadius' => 8,
                ],
            ],
            'labels' => $dates->map(fn (CarbonImmutable $date): string => $date->format('M d'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
