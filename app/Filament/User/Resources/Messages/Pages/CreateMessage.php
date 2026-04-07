<?php

namespace App\Filament\User\Resources\Messages\Pages;

use App\Enums\MessageType;
use App\Filament\User\Resources\Messages\MessageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMessage extends CreateRecord
{
    protected static string $resource = MessageResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['type'] ??= MessageType::Sms;

        return $data;
    }
}
