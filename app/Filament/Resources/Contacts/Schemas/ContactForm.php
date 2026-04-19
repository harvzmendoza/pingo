<?php

namespace App\Filament\Resources\Contacts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Owner')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn (string $operation): bool => $operation === 'create'),
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
                Select::make('groups')
                    ->label('Groups')
                    ->relationship('groups', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }
}
