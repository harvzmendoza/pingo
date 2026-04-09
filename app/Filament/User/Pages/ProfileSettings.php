<?php

namespace App\Filament\User\Pages;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class ProfileSettings extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 3;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Profile';

    protected static ?string $title = 'Profile';

    protected static ?string $slug = 'settings/profile';

    protected string $view = 'filament-panels::pages.page';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $user = Filament::auth()->user();

        $this->form->fill([
            'name' => $user?->name,
            'email' => $user?->email,
            'avatar_path' => $user?->avatar_path,
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
            ->id('profile-settings-form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make([
                    Action::make('save')
                        ->label('Save changes')
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
                FileUpload::make('avatar_path')
                    ->label('Avatar')
                    ->disk('public')
                    ->directory('avatars')
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->maxSize(2048)
                    ->columnSpanFull(),
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->required()
                    ->email()
                    ->maxLength(255),
                TextInput::make('current_password')
                    ->label('Current password')
                    ->password()
                    ->revealable()
                    ->autocomplete('current-password')
                    ->dehydrated(false),
                TextInput::make('new_password')
                    ->label('New password')
                    ->password()
                    ->revealable()
                    ->autocomplete('new-password')
                    ->dehydrated(false),
                TextInput::make('new_password_confirmation')
                    ->label('Confirm new password')
                    ->password()
                    ->revealable()
                    ->autocomplete('new-password')
                    ->dehydrated(false),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $user = Filament::auth()->user();
        if (! $user instanceof User) {
            return;
        }

        $state = $this->form->getState();

        validator($state, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($user->id),
            ],
            'avatar_path' => ['nullable', 'string', 'max:255'],
            'current_password' => ['nullable', 'string'],
            'new_password' => ['nullable', 'string', Password::defaults(), 'confirmed'],
        ], [], [
            'new_password' => 'new password',
        ])->validate();

        if (filled($state['new_password'] ?? null)) {
            if (! filled($state['current_password'] ?? null) || ! Hash::check((string) $state['current_password'], $user->password)) {
                Notification::make()
                    ->danger()
                    ->title('Password not changed')
                    ->body('Please enter your current password to set a new password.')
                    ->send();

                return;
            }
        }

        $user->forceFill([
            'name' => $state['name'],
            'email' => $state['email'],
            'avatar_path' => $state['avatar_path'] ?? null,
            ...filled($state['new_password'] ?? null) ? ['password' => $state['new_password']] : [],
        ])->save();

        Notification::make()
            ->success()
            ->title('Saved')
            ->body('Your profile has been updated.')
            ->send();

        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'avatar_path' => $user->avatar_path,
            'current_password' => null,
            'new_password' => null,
            'new_password_confirmation' => null,
        ]);
    }
}
