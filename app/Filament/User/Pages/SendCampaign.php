<?php

namespace App\Filament\User\Pages;

use App\Enums\MessageLogStatus;
use App\Enums\MessageType;
use App\Exceptions\SmsLimitReachedException;
use App\Jobs\SendScheduledCampaignJob;
use App\Models\Contact;
use App\Models\Group;
use App\Models\Message;
use App\Models\MessageLog;
use App\Models\User;
use App\Services\SendCampaignService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use UnitEnum;

class SendCampaign extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 4;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?string $title = 'Send Campaign';

    protected ?string $heading = '';

    protected string $view = 'filament.user.pages.send-campaign-wizard';

    public int $wizardStep = 1;

    public string $audience = 'contacts';

    /**
     * @var list<int|string>
     */
    public array $contactIds = [];

    /**
     * @var list<int|string>
     */
    public array $groupIds = [];

    public string $content = '';

    public bool $scheduleCampaign = false;

    public ?string $scheduledDate = null;

    public ?string $scheduledTime = null;

    public function mount(): void
    {
        $this->resetWizardState();
    }

    public function resetWizardState(): void
    {
        $this->wizardStep = 1;
        $this->audience = 'contacts';
        $this->contactIds = [];
        $this->groupIds = [];
        $this->content = '';
        $this->scheduleCampaign = false;
        $this->scheduledDate = null;
        $this->scheduledTime = null;
    }

    public function updatedAudience(string $value): void
    {
        if ($value !== 'contacts') {
            $this->contactIds = [];
        }
        if ($value !== 'groups') {
            $this->groupIds = [];
        }
    }

    public function nextStep(): void
    {
        $this->validateStep($this->wizardStep);

        if ($this->wizardStep < 4) {
            $this->wizardStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->wizardStep > 1) {
            $this->wizardStep--;
        }
    }

    public function goToStep(int $step): void
    {
        if ($step < 1 || $step > 4 || $step > $this->wizardStep) {
            return;
        }

        $this->wizardStep = $step;
    }

    public function send(SendCampaignService $sendCampaignService): void
    {
        $this->validateStep(1);
        $this->validateStep(2);
        $this->validateStep(3);

        $user = Filament::auth()->user();
        if (! $user instanceof User) {
            return;
        }

        $contactIds = $this->resolveTargetContactIds($user);

        if ($contactIds->isEmpty()) {
            Notification::make()
                ->danger()
                ->title('No recipients')
                ->body('Choose an audience that includes at least one contact.')
                ->send();

            return;
        }

        if ($this->scheduleCampaign) {
            $campaignTimezone = $this->getCampaignTimezone();
            $scheduledFor = Carbon::parse(sprintf(
                '%s %s',
                (string) $this->scheduledDate,
                (string) $this->scheduledTime,
            ), $campaignTimezone);

            if ($scheduledFor->isPast()) {
                Notification::make()
                    ->danger()
                    ->title('Invalid schedule time')
                    ->body('Please choose a future date and time for this campaign ('.$campaignTimezone.').')
                    ->send();

                return;
            }

            $message = Message::query()->create([
                'user_id' => $user->id,
                'content' => $this->content,
                'type' => MessageType::Sms,
            ]);

            foreach ($contactIds as $contactId) {
                MessageLog::query()->create([
                    'message_id' => $message->id,
                    'contact_id' => $contactId,
                    'status' => MessageLogStatus::Ongoing,
                    'response' => null,
                    'provider_message_id' => null,
                    'error_message' => null,
                    'sent_at' => null,
                ]);
            }

            SendScheduledCampaignJob::dispatch(
                messageId: $message->id,
                contactIds: $contactIds->all(),
            )->delay($scheduledFor->copy()->utc());

            Notification::make()
                ->success()
                ->title('Campaign scheduled')
                ->body('Campaign queued for '.$contactIds->count().' contact(s). It will send on '.$scheduledFor->format('M d, Y h:i A').' ('.$campaignTimezone.').')
                ->send();

            $this->resetWizardState();

            return;
        }

        try {
            $result = $sendCampaignService->send(
                user: $user,
                content: $this->content,
                contactIds: $contactIds->all(),
                sendToAllContacts: $this->audience === 'all',
            );
        } catch (SmsLimitReachedException $exception) {
            Notification::make()
                ->danger()
                ->title('Unable to send campaign')
                ->body($exception->getMessage())
                ->send();

            return;
        }

        Notification::make()
            ->success()
            ->title('Campaign sent')
            ->body(sprintf(
                'Sent: %d. Failed: %d. Skipped: %d.',
                $result['sent'],
                $result['failed'],
                $result['skipped'],
            ))
            ->send();

        $this->resetWizardState();
    }

    /**
     * @return EloquentCollection<int, Contact>
     */
    public function getContactsForWizard(): EloquentCollection
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

    /**
     * @return EloquentCollection<int, Group>
     */
    public function getGroupsForWizard(): EloquentCollection
    {
        $user = Filament::auth()->user();
        if (! $user instanceof User) {
            return new EloquentCollection;
        }

        return Group::query()
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get();
    }

    public function getCampaignTimezoneLabel(): string
    {
        return $this->getCampaignTimezone();
    }

    public function getAudienceSummary(): string
    {
        return match ($this->audience) {
            'all' => 'All contacts',
            'groups' => 'Groups: '.$this->getSelectedGroupNames()->implode(', ') ?: '—',
            default => 'Selected contacts ('.count($this->contactIds).')',
        };
    }

    public function getTimingSummary(): string
    {
        if (! $this->scheduleCampaign) {
            return 'Send immediately';
        }

        $tz = $this->getCampaignTimezone();

        try {
            $at = Carbon::parse(sprintf('%s %s', (string) $this->scheduledDate, (string) $this->scheduledTime), $tz);

            return 'Scheduled for '.$at->format('M d, Y h:i A').' ('.$tz.')';
        } catch (\Throwable) {
            return 'Scheduled';
        }
    }

    public function getRecipientCount(): int
    {
        $user = Filament::auth()->user();
        if (! $user instanceof User) {
            return 0;
        }

        return $this->resolveTargetContactIds($user)->count();
    }

    private function validateStep(int $step): void
    {
        $user = Filament::auth()->user();
        if (! $user instanceof User) {
            abort(403);
        }

        match ($step) {
            1 => $this->validateAudienceStep($user),
            2 => $this->validate([
                'content' => ['required', 'string', 'max:160'],
            ]),
            3 => $this->validate([
                'scheduleCampaign' => ['boolean'],
                'scheduledDate' => ['required_if:scheduleCampaign,true', 'nullable', 'date'],
                'scheduledTime' => ['required_if:scheduleCampaign,true', 'nullable', 'date_format:H:i'],
            ]),
            default => null,
        };

        if ($step === 3 && $this->scheduleCampaign) {
            $tz = $this->getCampaignTimezone();
            $scheduledFor = Carbon::parse(sprintf(
                '%s %s',
                (string) $this->scheduledDate,
                (string) $this->scheduledTime,
            ), $tz);

            if ($scheduledFor->isPast()) {
                throw ValidationException::withMessages([
                    'scheduledTime' => 'Choose a future date and time ('.$tz.').',
                ]);
            }
        }
    }

    private function validateAudienceStep(User $user): void
    {
        $this->validate([
            'audience' => ['required', Rule::in(['all', 'contacts', 'groups'])],
        ]);

        if ($this->audience === 'contacts') {
            $ids = $this->normalizedIntIds($this->contactIds);
            if ($ids->isEmpty()) {
                throw ValidationException::withMessages([
                    'contactIds' => 'Select at least one contact.',
                ]);
            }

            $validCount = Contact::query()
                ->where('user_id', $user->id)
                ->whereIn('id', $ids->all())
                ->count();

            if ($validCount !== $ids->count()) {
                throw ValidationException::withMessages([
                    'contactIds' => 'One or more selected contacts are invalid.',
                ]);
            }
        }

        if ($this->audience === 'groups') {
            $groupIds = $this->normalizedIntIds($this->groupIds);
            if ($groupIds->isEmpty()) {
                throw ValidationException::withMessages([
                    'groupIds' => 'Select at least one group.',
                ]);
            }

            $validCount = Group::query()
                ->where('user_id', $user->id)
                ->whereIn('id', $groupIds->all())
                ->count();

            if ($validCount !== $groupIds->count()) {
                throw ValidationException::withMessages([
                    'groupIds' => 'One or more selected groups are invalid.',
                ]);
            }

            if ($this->resolveTargetContactIds($user)->isEmpty()) {
                throw ValidationException::withMessages([
                    'groupIds' => 'No contacts belong to the selected groups. Add contacts to a group or choose a different audience.',
                ]);
            }
        }
    }

    /**
     * @param  list<int|string>  $ids
     * @return Collection<int, int>
     */
    private function normalizedIntIds(array $ids): Collection
    {
        return collect($ids)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values();
    }

    private function getCampaignTimezone(): string
    {
        return (string) config('app.campaign_timezone', config('app.timezone', 'UTC'));
    }

    /**
     * @return Collection<int, int>
     */
    private function resolveTargetContactIds(User $user): Collection
    {
        if ($this->audience === 'all') {
            return Contact::query()
                ->where('user_id', $user->id)
                ->pluck('id')
                ->map(fn (mixed $id): int => (int) $id)
                ->values();
        }

        if ($this->audience === 'groups') {
            $groupIds = $this->normalizedIntIds($this->groupIds);

            return Contact::query()
                ->where('user_id', $user->id)
                ->whereHas('groups', fn ($query) => $query->whereIn('groups.id', $groupIds->all()))
                ->pluck('id')
                ->map(fn (mixed $id): int => (int) $id)
                ->unique()
                ->values();
        }

        return $this->normalizedIntIds($this->contactIds);
    }

    /**
     * @return Collection<int, string>
     */
    private function getSelectedGroupNames(): Collection
    {
        $user = Filament::auth()->user();
        if (! $user instanceof User) {
            return collect();
        }

        $ids = $this->normalizedIntIds($this->groupIds);
        if ($ids->isEmpty()) {
            return collect();
        }

        return Group::query()
            ->where('user_id', $user->id)
            ->whereIn('id', $ids->all())
            ->orderBy('name')
            ->pluck('name');
    }
}
