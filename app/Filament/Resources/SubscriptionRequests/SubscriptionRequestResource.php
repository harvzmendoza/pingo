<?php

namespace App\Filament\Resources\SubscriptionRequests;

use App\Filament\Resources\SubscriptionRequests\Pages\ListSubscriptionRequests;
use App\Filament\Resources\SubscriptionRequests\Pages\ViewSubscriptionRequest;
use App\Filament\Resources\SubscriptionRequests\Schemas\SubscriptionRequestForm;
use App\Filament\Resources\SubscriptionRequests\Schemas\SubscriptionRequestInfolist;
use App\Filament\Resources\SubscriptionRequests\Tables\SubscriptionRequestsTable;
use App\Models\SubscriptionRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SubscriptionRequestResource extends Resource
{
    protected static ?string $model = SubscriptionRequest::class;

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Subscription requests';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    public static function form(Schema $schema): Schema
    {
        return SubscriptionRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SubscriptionRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubscriptionRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptionRequests::route('/'),
            'view' => ViewSubscriptionRequest::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
