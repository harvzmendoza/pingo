<?php

namespace App\Filament\User\Resources\Messages;

use App\Filament\User\Resources\Messages\Pages\CreateMessage;
use App\Filament\User\Resources\Messages\Pages\EditMessage;
use App\Filament\User\Resources\Messages\Pages\ListMessages;
use App\Filament\User\Resources\Messages\Pages\ViewMessage;
use App\Filament\User\Resources\Messages\RelationManagers\MessageLogsRelationManager;
use App\Filament\User\Resources\Messages\Schemas\MessageForm;
use App\Filament\User\Resources\Messages\Schemas\MessageInfolist;
use App\Filament\User\Resources\Messages\Tables\MessagesTable;
use App\Models\Message;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static string|UnitEnum|null $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return MessageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MessageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MessagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MessageLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMessages::route('/'),
            'create' => CreateMessage::route('/create'),
            'view' => ViewMessage::route('/{record}'),
            'edit' => EditMessage::route('/{record}/edit'),
        ];
    }
}
