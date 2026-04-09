<?php

namespace App\Filament\Widgets;

use App\Enums\MessageLogStatus;
use App\Models\MessageLog;
use Filament\Widgets\ChartWidget;

class DeliveryStatusSplitChart extends ChartWidget
{
    protected ?string $heading = 'Delivery Status Split';

    protected ?string $pollingInterval = '30s';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $sent = MessageLog::query()
            ->where('status', MessageLogStatus::Sent)
            ->count();

        $failed = MessageLog::query()
            ->where('status', MessageLogStatus::Failed)
            ->count();

        return [
            'datasets' => [[
                'data' => [$sent, $failed],
                'backgroundColor' => ['#16A34A', '#DC2626'],
            ]],
            'labels' => ['Sent', 'Failed'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
