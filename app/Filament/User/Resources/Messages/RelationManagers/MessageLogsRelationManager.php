<?php

namespace App\Filament\User\Resources\Messages\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MessageLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'messageLogs';

    protected static ?string $title = 'Message logs';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['contact']))
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('contact.name')
                    ->label('Contact')
                    ->searchable(),
                TextColumn::make('contact.phone_number')
                    ->label('Phone'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('provider_message_id')
                    ->label('Provider ID')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('response')
                    ->limit(48)
                    ->tooltip(fn (?string $state): ?string => $state),
                TextColumn::make('error_message')
                    ->label('Error')
                    ->limit(40)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sent_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('sent_at', 'desc');
    }
}
