<?php

namespace App\Filament\User\Widgets;

use App\Enums\MessageLogStatus;
use App\Models\Contact;
use App\Models\MessageLog;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class MessagingStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $user = Filament::auth()->user();
        $userId = $user?->getAuthIdentifier();

        if (! $userId) {
            return [
                Stat::make('Total Contacts', 0),
                Stat::make('Messages Sent Today', 0),
                Stat::make('Success Rate', '0.00%'),
            ];
        }

        $totalContacts = Contact::query()
            ->where('user_id', $userId)
            ->count();

        $todayLogsQuery = MessageLog::query()
            ->whereHas('message', fn (Builder $query): Builder => $query->where('user_id', $userId))
            ->whereDate('sent_at', today());

        $sentToday = (clone $todayLogsQuery)
            ->where('status', MessageLogStatus::Sent)
            ->count();

        $failedToday = (clone $todayLogsQuery)
            ->where('status', MessageLogStatus::Failed)
            ->count();

        $attemptedToday = $sentToday + $failedToday;
        $successRate = $attemptedToday > 0
            ? ($sentToday / $attemptedToday) * 100
            : 0.0;

        return [
            Stat::make('Total Contacts', Number::format($totalContacts))
                ->description('Contacts in your account')
                ->color('primary'),
            Stat::make('Messages Sent Today', Number::format($sentToday))
                ->description('Successful deliveries today')
                ->color($sentToday > 0 ? 'success' : 'gray'),
            Stat::make('Success Rate', number_format($successRate, 2).'%')
                ->description('Delivery success rate today')
                ->color($successRate >= 90 ? 'success' : ($successRate >= 70 ? 'warning' : 'danger')),
        ];
    }
}
