<?php

namespace App\Filament\User\Resources\Contacts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ContactInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('phone_number')
                    ->label('Phone number'),
                TextEntry::make('email')
                    ->placeholder('—'),
            ]);
    }
}
