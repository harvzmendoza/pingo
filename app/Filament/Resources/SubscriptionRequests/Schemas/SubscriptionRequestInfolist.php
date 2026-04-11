<?php

namespace App\Filament\Resources\SubscriptionRequests\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SubscriptionRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('Customer'),
                TextEntry::make('user.email')
                    ->label('Email'),
                TextEntry::make('plan.name')
                    ->label('Plan'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('payer_name'),
                TextEntry::make('payment_reference'),
                TextEntry::make('notes')
                    ->columnSpanFull(),
                ImageEntry::make('payment_screenshot_path')
                    ->label('Payment screenshot')
                    ->disk('public')
                    ->visible(fn ($record): bool => filled($record->payment_screenshot_path))
                    ->columnSpanFull(),
                TextEntry::make('admin_note')
                    ->columnSpanFull()
                    ->visible(fn ($record): bool => filled($record->admin_note)),
                TextEntry::make('reviewer.name')
                    ->label('Reviewed by')
                    ->visible(fn ($record): bool => $record->reviewed_at !== null),
                TextEntry::make('reviewed_at')
                    ->dateTime()
                    ->visible(fn ($record): bool => $record->reviewed_at !== null),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }
}
