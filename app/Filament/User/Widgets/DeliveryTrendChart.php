<?php

namespace App\Filament\User\Widgets;

use App\Enums\MessageLogStatus;
use App\Models\MessageLog;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class DeliveryTrendChart extends ChartWidget
{
    protected ?string $heading = 'Delivery Trend (Last 7 Days)';

    protected ?string $pollingInterval = '30s';

    protected static ?int $sort = 5;
    
    protected function getData(): array
    {
        $userId = Filament::auth()->id();

        $dates = collect(range(6, 0))
            ->map(fn (int $daysAgo): CarbonImmutable => CarbonImmutable::today()->subDays($daysAgo));

        if (! $userId) {
            return [
                'datasets' => [
                    ['label' => 'Sent', 'data' => array_fill(0, 7, 0)],
                    ['label' => 'Failed', 'data' => array_fill(0, 7, 0)],
                ],
                'labels' => $dates->map(fn (CarbonImmutable $date): string => $date->format('M d'))->all(),
            ];
        }

        $rows = MessageLog::query()
            ->selectRaw('DATE(sent_at) as sent_date, status, COUNT(*) as total')
            ->whereHas('message', fn (Builder $query): Builder => $query->where('user_id', $userId))
            ->whereDate('sent_at', '>=', CarbonImmutable::today()->subDays(6))
            ->groupBy('sent_date', 'status')
            ->get();

        $sentSeries = $dates->map(function (CarbonImmutable $date) use ($rows): int {
            return (int) ($rows
                ->first(fn (MessageLog $row): bool => $row->sent_date === $date->toDateString() && $row->status === MessageLogStatus::Sent->value)
                ->total ?? 0);
        });

        $failedSeries = $dates->map(function (CarbonImmutable $date) use ($rows): int {
            return (int) ($rows
                ->first(fn (MessageLog $row): bool => $row->sent_date === $date->toDateString() && $row->status === MessageLogStatus::Failed->value)
                ->total ?? 0);
        });

        return [
            'datasets' => [
                [
                    'label' => 'Sent',
                    'data' => $sentSeries->all(),
                    'borderColor' => '#16A34A',
                    'backgroundColor' => 'rgba(22, 163, 74, 0.15)',
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Failed',
                    'data' => $failedSeries->all(),
                    'borderColor' => '#DC2626',
                    'backgroundColor' => 'rgba(220, 38, 38, 0.15)',
                    'tension' => 0.35,
                ],
            ],
            'labels' => $dates->map(fn (CarbonImmutable $date): string => $date->format('M d'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
