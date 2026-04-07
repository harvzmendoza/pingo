<?php

namespace App\Filament\User\Resources\Messages\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('content')
                    ->required()
                    ->rows(6)
                    ->columnSpanFull(),
            ]);
    }
}
