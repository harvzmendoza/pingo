<?php

namespace App\Filament\User\Pages;

use App\Exceptions\SmsLimitReachedException;
use App\Models\Contact;
use App\Models\User;
use App\Services\SendCampaignService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Validation\ValidationException;
use UnitEnum;

class SendMessage extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 3;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static ?string $title = 'Send Message';

    protected ?string $heading = '';

    protected string $view = 'filament.user.pages.send-message';

    public ?int $contactId = null;

    public string $contactSearch = '';

    public string $content = '';

    /**
     * @return EloquentCollection<int, Contact>
     */
    public function getContactsForSendMessage(): EloquentCollection
    {
        $user = Filament::auth()->user();

        if (! $user instanceof User) {
            return new EloquentCollection;
        }

        return Contact::query()
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get();
    }

    public function getSelectedContactLabel(): string
    {
        if (! $this->contactId) {
            return '—';
        }

        $contact = $this->getContactsForSendMessage()
            ->firstWhere('id', $this->contactId);

        if (! $contact instanceof Contact) {
            return '—';
        }

        return sprintf('%s (%s)', $contact->name, $contact->phone_number);
    }

    public function updatedContactSearch(string $value): void
    {
        $filteredContacts = $this->getFilteredContactsForSendMessage();

        if ($filteredContacts->count() === 1) {
            $onlyContact = $filteredContacts->first();

            if ($onlyContact instanceof Contact) {
                $this->contactId = $onlyContact->id;
            }

            return;
        }

        if ($value === '') {
            return;
        }

        if ($this->contactId === null) {
            return;
        }

        $selectedStillVisible = $filteredContacts
            ->contains(fn (Contact $contact): bool => $contact->id === $this->contactId);

        if (! $selectedStillVisible) {
            $this->contactId = null;
        }
    }

    /**
     * @return EloquentCollection<int, Contact>
     */
    public function getFilteredContactsForSendMessage(): EloquentCollection
    {
        $contacts = $this->getContactsForSendMessage();
        $search = trim($this->contactSearch);

        if ($search === '') {
            return $contacts;
        }

        $search = mb_strtolower($search);

        return $contacts
            ->filter(
                fn (Contact $contact): bool => str_contains(mb_strtolower($contact->name), $search)
                    || str_contains(mb_strtolower($contact->phone_number), $search)
                    || str_contains(mb_strtolower((string) $contact->email), $search)
            )
            ->values();
    }

    public function send(SendCampaignService $sendCampaignService): void
    {
        $user = Filament::auth()->user();

        if (! $user instanceof User) {
            return;
        }

        $this->validate([
            'contactId' => ['required', 'integer'],
            'content' => ['required', 'string', 'max:160'],
        ]);

        $contact = Contact::query()
            ->where('user_id', $user->id)
            ->whereKey($this->contactId)
            ->first();

        if (! $contact instanceof Contact) {
            throw ValidationException::withMessages([
                'contactId' => 'Select a valid contact from your list.',
            ]);
        }

        try {
            $result = $sendCampaignService->send(
                user: $user,
                content: $this->content,
                contactIds: [$contact->id],
                sendToAllContacts: false,
            );
        } catch (SmsLimitReachedException $exception) {
            Notification::make()
                ->danger()
                ->title('Unable to send message')
                ->body($exception->getMessage())
                ->send();

            return;
        }

        Notification::make()
            ->success()
            ->title('Message sent')
            ->body(sprintf(
                'Sent: %d. Failed: %d. Skipped: %d.',
                $result['sent'],
                $result['failed'],
                $result['skipped'],
            ))
            ->send();

        $this->content = '';
    }
}
