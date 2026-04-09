<?php

namespace App\Filament\Widgets;

use App\Enums\MessageLogStatus;
use App\Models\Contact;
use App\Models\Message;
use App\Models\MessageLog;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class PlatformMessagingStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalUsers = User::query()->count();
        $totalContacts = Contact::query()->count();
        $totalMessages = Message::query()->count();
        $totalLogs = MessageLog::query()->count();

        $sentLogs = MessageLog::query()
            ->where('status', MessageLogStatus::Sent)
            ->count();

        $failedLogs = MessageLog::query()
            ->where('status', MessageLogStatus::Failed)
            ->count();

        $attempted = $sentLogs + $failedLogs;
        $successRate = $attempted > 0 ? ($sentLogs / $attempted) * 100 : 0.0;

        return [
            Stat::make('Total Users', Number::format($totalUsers))
                ->icon(Heroicon::Users)
                ->color('info'),
            Stat::make('Total Contacts', Number::format($totalContacts))
                ->icon(Heroicon::ChatBubbleLeftRight)
                ->color('primary'),
            Stat::make('Total Messages', Number::format($totalMessages))
                ->icon(Heroicon::ChatBubbleLeftRight)
                ->color('warning'),
            Stat::make('Delivery Success Rate', number_format($successRate, 2).'%')
                ->description('Sent: '.Number::format($sentLogs).' | Failed: '.Number::format($failedLogs))
                ->icon(Heroicon::ChartBar)
                ->color($successRate >= 90 ? 'success' : ($successRate >= 70 ? 'warning' : 'danger')),
            Stat::make('Delivery Attempts', Number::format($totalLogs))
                ->icon(Heroicon::PaperAirplane)
                ->color('gray'),
        ];
    }
}
