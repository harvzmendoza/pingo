<?php

namespace App\Filament\User\Resources\Messages\Pages;

use App\Filament\User\Resources\Messages\Concerns\ConfiguresSendSmsToContactsAction;
use App\Filament\User\Resources\Messages\MessageResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMessage extends EditRecord
{
    use ConfiguresSendSmsToContactsAction;

    protected static string $resource = MessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->sendSmsToContactsAction(),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
