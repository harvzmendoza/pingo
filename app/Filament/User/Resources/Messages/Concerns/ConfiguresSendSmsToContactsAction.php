<?php

namespace App\Filament\User\Resources\Messages\Concerns;

use App\Models\Contact;
use App\Models\Message;
use App\Services\MessageDispatchService;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

trait ConfiguresSendSmsToContactsAction
{
    protected function sendSmsToContactsAction(): Action
    {
        return Action::make('sendSmsToContacts')
            ->label('Send to contacts')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->schema([
                CheckboxList::make('contact_ids')
                    ->label('Recipients')
                    ->options(fn (): array => Contact::query()
                        ->where('user_id', auth()->id())
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (Contact $contact): array => [
                            $contact->id => "{$contact->name} ({$contact->phone_number})",
                        ])
                        ->all())
                    ->required()
                    ->columns(2),
            ])
            ->action(function (array $data): void {
                /** @var Message $message */
                $message = $this->record;

                $ids = $data['contact_ids'] ?? [];
                if (! is_array($ids) || $ids === []) {
                    Notification::make()
                        ->danger()
                        ->title('Select at least one contact')
                        ->send();

                    return;
                }

                $dispatch = app(MessageDispatchService::class);
                $result = $dispatch->sendToContacts($message, $ids);

                Notification::make()
                    ->success()
                    ->title('SMS dispatch finished')
                    ->body(sprintf(
                        'Sent: %d. Failed: %d. Skipped (not your contacts): %d.',
                        $result['sent'],
                        $result['failed'],
                        $result['skipped'],
                    ))
                    ->send();
            });
    }
}
