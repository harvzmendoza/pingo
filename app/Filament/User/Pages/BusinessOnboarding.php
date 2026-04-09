<?php

namespace App\Filament\User\Pages;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class BusinessOnboarding extends Page
{
    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = null;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'business-onboarding';

    protected static ?string $title = 'Business Setup';

    protected string $view = 'filament-panels::pages.page';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        /** @var User|null $user */
        $user = Filament::auth()->user();

        $this->form->fill([
            'business_name' => $user?->business_name,
            'business_description' => $user?->business_description,
            'business_category' => $user?->business_category,
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
            ->id('business-onboarding-form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make([
                    Action::make('save')
                        ->label('Complete setup')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->submit('save')
                        ->color('primary'),
                ])->fullWidth(),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Business details')
                        ->schema([
                            TextInput::make('business_name')
                                ->label('Business name')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('business_description')
                                ->label('Business description')
                                ->rows(4)
                                ->required()
                                ->maxLength(1000),
                        ])
                        ->columns(1),
                    Step::make('Business category')
                        ->schema([
                            Select::make('business_category')
                                ->label('Category')
                                ->required()
                                ->options([
                                    'restaurant' => 'Restaurant',
                                    'salon' => 'Salon / Barbershop',
                                    'clinic' => 'Clinic / Dental',
                                    'retail' => 'Retail / Boutique',
                                    'services' => 'Services',
                                    'fitness' => 'Fitness / Gym',
                                    'education' => 'Education',
                                    'other' => 'Other',
                                ])
                                ->searchable(),
                        ])
                        ->columns(1),
                ])->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $user = Filament::auth()->user();
        if (! $user instanceof User) {
            return;
        }

        $user->forceFill([
            'business_name' => $state['business_name'],
            'business_description' => $state['business_description'],
            'business_category' => $state['business_category'],
            'onboarding_completed_at' => now(),
        ])->save();

        Notification::make()
            ->success()
            ->title('Setup completed')
            ->body('Your business profile is ready. Welcome to your dashboard.')
            ->send();

        $this->redirect(Dashboard::getUrl(panel: 'user'), navigate: true);
    }
}
