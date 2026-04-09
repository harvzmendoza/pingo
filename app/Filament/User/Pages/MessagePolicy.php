<?php

namespace App\Filament\User\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class MessagePolicy extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 5;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static ?string $title = 'Message Policy';

    protected static ?string $slug = 'message-policy';

    protected string $view = 'filament-panels::pages.page';

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Callout::make('Policy summary')
                    ->description('Messages that violate the policy may still show as “sent”, but they will not be delivered. Penalty credits may also apply.')
                    ->warning(),

                Callout::make('URLs / links are prohibited')
                    ->description('Any URLs, domains, IPs, or shortened links (e.g. http, www, bit.ly) are not allowed. Penalty: 10 credits per message.')
                    ->danger()
                    ->color(null),

                Callout::make('Profanity is prohibited')
                    ->description('Filipino (Tagalog, Bisaya) or English profanity, insults, and bullying are not allowed. Penalty: 50 credits per message.')
                    ->danger()
                    ->color(null),

                Callout::make('Bulk campaigns')
                    ->description('For bulk sends, the penalty is charged per recipient (e.g. a URL to 10 recipients = 100 credits penalty).')
                    ->info(),
            ]);
    }
}
