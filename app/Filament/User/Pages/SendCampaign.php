<?php

namespace App\Filament\User\Pages;

use App\Enums\MessageLogStatus;
use App\Enums\MessageType;
use App\Jobs\SendScheduledCampaignJob;
use App\Models\Contact;
use App\Models\Message;
use App\Models\MessageLog;
use App\Models\User;
use App\Services\SendCampaignService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class SendCampaign extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 4;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?string $title = 'Send Campaign';

    protected string $view = 'filament-panels::pages.page';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'send_to_all_contacts' => false,
            'contact_ids' => [],
            'schedule_campaign' => false,
            'scheduled_date' => null,
            'scheduled_time' => null,
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    protected function getFormContentComponent(): Form
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('send-campaign-form')
            ->livewireSubmitHandler('send')
            ->footer([
                Actions::make([
                    Action::make('send')
                        ->label('Send')
                        ->icon(Heroicon::OutlinedPaperAirplane)
                        ->submit('send')
                        ->color('primary'),
                ])->fullWidth(),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Callout::make('Content policy reminder')
                    ->description('Avoid URLs/links and profanity. Violations may still show as sent, but messages may not be delivered and penalty credits may apply. Bulk penalties are charged per recipient.')
                    ->info()
                    ->actions([
                        Action::make('viewContentPolicy')
                            ->label('View content policy')
                            ->url(MessagePolicy::getUrl(panel: 'user')),
                    ])
                    ->columnSpanFull(),
                Toggle::make('send_to_all_contacts')
                    ->label('Send to all contacts')
                    ->helperText('Enable this to send to your full contact list.')
                    ->live(),
                Toggle::make('schedule_campaign')
                    ->label('Schedule campaign')
                    ->helperText('Enable this to send the campaign at a specific date and time.')
                    ->live(),
                DatePicker::make('scheduled_date')
                    ->label('Send date')
                    ->helperText(fn (): string => 'Choose the campaign date ('.$this->getCampaignTimezone().').')
                    ->minDate(fn () => now($this->getCampaignTimezone())->toDateString())
                    ->visible(fn (Get $get): bool => (bool) $get('schedule_campaign'))
                    ->required(fn (Get $get): bool => (bool) $get('schedule_campaign')),
                TimePicker::make('scheduled_time')
                    ->label('Send time')
                    ->helperText(fn (): string => 'Choose the campaign time ('.$this->getCampaignTimezone().').')
                    ->seconds(false)
                    ->visible(fn (Get $get): bool => (bool) $get('schedule_campaign'))
                    ->required(fn (Get $get): bool => (bool) $get('schedule_campaign')),
                CheckboxList::make('contact_ids')
                    ->label('Select contacts')
                    ->options(fn (): array => Contact::query()
                        ->where('user_id', Filament::auth()->id())
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (Contact $contact): array => [
                            $contact->id => "{$contact->name} ({$contact->phone_number})",
                        ])
                        ->all())
                    ->columns(2)
                    ->searchable()
                    ->visible(fn (Get $get): bool => ! (bool) $get('send_to_all_contacts'))
                    ->required(fn (Get $get): bool => ! (bool) $get('send_to_all_contacts')),
                Textarea::make('content')
                    ->label('Message')
                    ->required()
                    ->live()
                    ->maxLength(160)
                    ->helperText(fn (Get $get): string => strlen((string) ($get('content') ?? '')).' / 160 characters')
                    ->rows(5)
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function send(SendCampaignService $sendCampaignService): void
    {
        $state = $this->form->getState();

        $user = Filament::auth()->user();
        if (! $user instanceof User) {
            return;
        }

        $scheduleCampaign = (bool) ($state['schedule_campaign'] ?? false);
        if ($scheduleCampaign) {
            $campaignTimezone = $this->getCampaignTimezone();
            $scheduledFor = Carbon::parse(sprintf(
                '%s %s',
                (string) ($state['scheduled_date'] ?? ''),
                (string) ($state['scheduled_time'] ?? ''),
            ), $campaignTimezone);

            if ($scheduledFor->isPast()) {
                Notification::make()
                    ->danger()
                    ->title('Invalid schedule time')
                    ->body('Please choose a future date and time for this campaign ('.$campaignTimezone.').')
                    ->send();

                return;
            }

            $contactIds = $this->resolveTargetContactIds(
                user: $user,
                contactIds: (array) ($state['contact_ids'] ?? []),
                sendToAllContacts: (bool) ($state['send_to_all_contacts'] ?? false),
            );

            $message = Message::query()->create([
                'user_id' => $user->id,
                'content' => (string) $state['content'],
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

            $this->form->fill([
                'send_to_all_contacts' => false,
                'contact_ids' => [],
                'content' => null,
                'schedule_campaign' => false,
                'scheduled_date' => null,
                'scheduled_time' => null,
            ]);

            return;
        }

        $result = $sendCampaignService->send(
            user: $user,
            content: (string) $state['content'],
            contactIds: $state['contact_ids'] ?? [],
            sendToAllContacts: (bool) ($state['send_to_all_contacts'] ?? false),
        );

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

        $this->form->fill([
            'send_to_all_contacts' => false,
            'contact_ids' => [],
            'content' => null,
            'schedule_campaign' => false,
            'scheduled_date' => null,
            'scheduled_time' => null,
        ]);
    }

    private function getCampaignTimezone(): string
    {
        return (string) config('app.campaign_timezone', config('app.timezone', 'UTC'));
    }

    /**
     * @param  array<int, mixed>  $contactIds
     * @return Collection<int, int>
     */
    private function resolveTargetContactIds(User $user, array $contactIds, bool $sendToAllContacts): Collection
    {
        if ($sendToAllContacts) {
            return Contact::query()
                ->where('user_id', $user->id)
                ->pluck('id')
                ->map(fn (mixed $id): int => (int) $id)
                ->values();
        }

        return collect($contactIds)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter()
            ->values();
    }
}
