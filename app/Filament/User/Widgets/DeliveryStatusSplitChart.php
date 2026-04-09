<?php

namespace App\Filament\User\Widgets;

use App\Enums\MessageLogStatus;
use App\Models\MessageLog;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class DeliveryStatusSplitChart extends ChartWidget
{
    protected ?string $heading = 'Delivery Status Split (Last 30 Days)';

    protected ?string $pollingInterval = '30s';

    protected static ?int $sort = 4;
    
    protected function getData(): array
    {
        $userId = Filament::auth()->id();

        if (! $userId) {
            return [
                'datasets' => [
                    ['data' => [0, 0]],
                ],
                'labels' => ['Sent', 'Failed'],
            ];
        }

        $sent = MessageLog::query()
            ->whereHas('message', fn (Builder $query): Builder => $query->where('user_id', $userId))
            ->whereDate('sent_at', '>=', CarbonImmutable::today()->subDays(29))
            ->where('status', MessageLogStatus::Sent)
            ->count();

        $failed = MessageLog::query()
            ->whereHas('message', fn (Builder $query): Builder => $query->where('user_id', $userId))
            ->whereDate('sent_at', '>=', CarbonImmutable::today()->subDays(29))
            ->where('status', MessageLogStatus::Failed)
            ->count();

        return [
            'datasets' => [
                [
                    'data' => [$sent, $failed],
                    'backgroundColor' => ['#16A34A', '#DC2626'],
                ],
            ],
            'labels' => ['Sent', 'Failed'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
