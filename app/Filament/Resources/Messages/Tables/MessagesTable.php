<?php

namespace App\Filament\Resources\Messages\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['user']))
            ->columns([
                TextColumn::make('content')
                    ->limit(70)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->searchable(),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('message_logs_count')
                    ->counts('messageLogs')
                    ->label('Logs'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
