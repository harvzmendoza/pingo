<?php

namespace App\Filament\User\Pages;

use App\Models\Contact;
use App\Models\User;
use App\Services\SendCampaignService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
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
                            ->url('https://skysms.skyio.site/docs#content-policy', shouldOpenInNewTab: true),
                    ])
                    ->columnSpanFull(),
                Toggle::make('send_to_all_contacts')
                    ->label('Send to all contacts')
                    ->helperText('Enable this to send to your full contact list.')
                    ->live(),
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
                    ->maxLength(160)
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
        ]);
    }
}
