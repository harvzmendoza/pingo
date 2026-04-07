<?php

namespace App\Filament\User\Resources\Messages\Pages;

use App\Filament\User\Resources\Messages\Concerns\ConfiguresSendSmsToContactsAction;
use App\Filament\User\Resources\Messages\MessageResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMessage extends ViewRecord
{
    use ConfiguresSendSmsToContactsAction;

    protected static string $resource = MessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->sendSmsToContactsAction(),
            EditAction::make(),
        ];
    }
}
