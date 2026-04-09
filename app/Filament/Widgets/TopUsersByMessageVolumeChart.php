<?php

namespace App\Filament\Widgets;

use App\Models\Message;
use Filament\Widgets\ChartWidget;

class TopUsersByMessageVolumeChart extends ChartWidget
{
    protected ?string $heading = 'Top Users by Messages';

    protected ?string $pollingInterval = '30s';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $rows = Message::query()
            ->selectRaw('user_id, COUNT(*) as total')
            ->with('user:id,name')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'datasets' => [[
                'label' => 'Messages',
                'data' => $rows->pluck('total')->map(fn ($value): int => (int) $value)->all(),
                'backgroundColor' => '#6366F1',
            ]],
            'labels' => $rows->map(fn (Message $message): string => $message->user?->name ?? 'Unknown')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
