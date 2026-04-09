<?php

namespace App\Filament\User\Widgets;

use App\Models\Contact;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class ContactsGrowthChart extends ChartWidget
{
    protected ?string $heading = 'New Contacts (Last 7 Days)';

    protected ?string $pollingInterval = '30s';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $userId = Filament::auth()->id();

        $dates = collect(range(6, 0))
            ->map(fn (int $daysAgo): CarbonImmutable => CarbonImmutable::today()->subDays($daysAgo));

        if (! $userId) {
            return [
                'datasets' => [
                    ['label' => 'Contacts', 'data' => array_fill(0, 7, 0)],
                ],
                'labels' => $dates->map(fn (CarbonImmutable $date): string => $date->format('M d'))->all(),
            ];
        }

        $rows = Contact::query()
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
                    'label' => 'Contacts',
                    'data' => $series->all(),
                    'backgroundColor' => 'rgba(14, 165, 233, 0.65)',
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
