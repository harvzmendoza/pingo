<?php

namespace App\Filament\User\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class MessagePolicy extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 5;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static ?string $title = 'Message Policy';

    protected static ?string $slug = 'message-policy';

    protected string $view = 'filament.user.pages.message-policy';
}
