<?php

namespace App\Filament\Resources\Contacts\Schemas;

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
                TextEntry::make('user.name')
                    ->label('Owner'),
                TextEntry::make('groups.name')
                    ->label('Groups')
                    ->badge()
                    ->separator(', ')
                    ->placeholder('—'),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }
}
