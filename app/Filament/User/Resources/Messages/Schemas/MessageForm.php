<?php

namespace App\Filament\User\Resources\Messages\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Schema;

class MessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Callout::make('Content policy reminder')
                    ->description('Avoid URLs/links and profanity. Violations may still show as sent, but messages may not be delivered and penalty credits may apply. Bulk penalties are charged per recipient.')
                    ->info()
                    ->actions([
                        Action::make('viewContentPolicy')
                            ->label('View content policy')
                            ->url('https://skysms.skyio.site/docs#content-policy', shouldOpenInNewTab: true),
                    ])
                    ->columnSpanFull(),
                Textarea::make('content')
                    ->required()
                    ->rows(6)
                    ->columnSpanFull(),
            ]);
    }
}
