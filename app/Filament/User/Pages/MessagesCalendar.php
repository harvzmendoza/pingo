<?php

namespace App\Filament\User\Pages;

use App\Filament\User\Widgets\MessagesCalendarWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class MessagesCalendar extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $title = 'Calendar';

    protected ?string $subheading = 'Indigo: saved messages. Amber: scheduled campaigns waiting in the queue.';

    protected Width|string|null $maxContentWidth = Width::Full;

    /**
     * @return array<class-string>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            MessagesCalendarWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    public function content(Schema $schema): Schema
    {
        return $schema;
    }
}
