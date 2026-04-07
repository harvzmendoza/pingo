<?php

namespace App\Filament\User\Resources\Contacts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone_number')
                    ->label('Phone number')
                    ->tel()
                    ->required()
                    ->maxLength(32),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255),
            ]);
    }
}
