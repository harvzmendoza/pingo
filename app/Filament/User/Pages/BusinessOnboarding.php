<?php

namespace App\Filament\User\Pages;

use App\Filament\User\Pages\User\SubscriptionPlans;
use App\Models\Contact;
use App\Models\Plan;
use App\Models\User;
use App\Services\SubscriptionService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use UnitEnum;

class BusinessOnboarding extends Page
{
    /**
     * Simple layout hides the main panel sidebar so onboarding stays focused.
     */
    protected static string $layout = 'filament-panels::components.layout.simple';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = null;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'business-onboarding';

    protected static ?string $title = 'Business Setup';

    protected ?string $heading = '';

    protected string $view = 'filament.user.pages.business-onboarding-wizard';

    protected Width|string|null $maxContentWidth = Width::Full;

    public int $wizardStep = 1;

    public string $business_name = '';

    public string $business_description = '';

    public string $business_category = '';

    /**
     * @var list<array{name: string, phone_number: string, email: string}>
     */
    public array $contactRows = [];

    public bool $startFreeTrial = true;

    public bool $isFinishing = false;

    /**
     * Required by the simple page wrapper; keep false so the wizard hero is the only headline.
     */
    public function hasLogo(): bool
    {
        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function getExtraBodyAttributes(): array
    {
        return [
            ...parent::getExtraBodyAttributes(),
            'class' => 'fi-body-business-onboarding',
        ];
    }

    public function mount(): void
    {
        /** @var User|null $user */
        $user = Filament::auth()->user();

        $this->business_name = $user?->business_name ?? '';
        $this->business_description = $user?->business_description ?? '';
        $this->business_category = $user?->business_category ?? '';
        $this->contactRows = [
            ['name' => '', 'phone_number' => '', 'email' => ''],
        ];
        $this->startFreeTrial = true;
        $this->wizardStep = 1;
        $this->isFinishing = false;
    }

    /**
     * @return array<string, string>
     */
    public function getBusinessCategoryOptions(): array
    {
        return [
            'restaurant' => 'Restaurant',
            'salon' => 'Salon / Barbershop',
            'clinic' => 'Clinic / Dental',
            'retail' => 'Retail / Boutique',
            'services' => 'Services',
            'fitness' => 'Fitness / Gym',
            'education' => 'Education',
            'other' => 'Other',
        ];
    }

    public function getBusinessCategoryLabel(): string
    {
        $options = $this->getBusinessCategoryOptions();

        return $options[$this->business_category] ?? '—';
    }

    public function getFilledContactRowCount(): int
    {
        $count = 0;

        foreach ($this->contactRows as $row) {
            if ($this->contactRowHasAnyInput($row) && $this->contactRowIsComplete($row)) {
                $count++;
            }
        }

        return $count;
    }

    public function canOfferFreeTrial(): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        $subscriptionService = app(SubscriptionService::class);

        if ($subscriptionService->userHasUsedFreePlan($user)) {
            return false;
        }

        $freePlan = Plan::query()->where('price', '<=', 0)->first();

        if (! $freePlan instanceof Plan) {
            return false;
        }

        $subscription = $subscriptionService->getCurrentSubscription($user);

        if ($subscription && ! $subscription->plan->isFree()) {
            return false;
        }

        return true;
    }

    public function addContactRow(): void
    {
        if (count($this->contactRows) >= 25) {
            return;
        }

        $this->contactRows[] = ['name' => '', 'phone_number' => '', 'email' => ''];
    }

    public function removeContactRow(int $index): void
    {
        if (count($this->contactRows) <= 1) {
            return;
        }

        unset($this->contactRows[$index]);
        $this->contactRows = array_values($this->contactRows);
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
        if ($step < 1 || $step > 4 || $step > $this->wizardStep || $this->isFinishing) {
            return;
        }

        $this->wizardStep = $step;
    }

    public function finishOnboarding(): void
    {
        if ($this->isFinishing) {
            return;
        }

        $this->validateStep(1);
        $this->validateStep(2);
        $this->validateStep(3);

        /** @var User|null $user */
        $user = Filament::auth()->user();

        if (! $user instanceof User) {
            return;
        }

        DB::transaction(function () use ($user): void {
            $user->forceFill([
                'business_name' => $this->business_name,
                'business_description' => $this->business_description,
                'business_category' => $this->business_category,
                'onboarding_completed_at' => now(),
            ])->save();

            foreach ($this->contactRows as $row) {
                if (! $this->contactRowHasAnyInput($row)) {
                    continue;
                }

                if (! $this->contactRowIsComplete($row)) {
                    continue;
                }

                Contact::query()->create([
                    'user_id' => $user->id,
                    'name' => trim($row['name']),
                    'phone_number' => trim($row['phone_number']),
                    'email' => filled($row['email'] ?? null) ? trim((string) $row['email']) : null,
                ]);
            }
        });

        if ($this->canOfferFreeTrial() && $this->startFreeTrial) {
            $freePlan = Plan::query()->where('price', '<=', 0)->first();

            if ($freePlan instanceof Plan) {
                $applied = app(SubscriptionService::class)->applyInstantFreeTrial($user, $freePlan);

                if (! $applied) {
                    Notification::make()
                        ->warning()
                        ->title('Free trial unavailable')
                        ->body('Your account is set up, but the free trial could not be started automatically. You can pick a plan from Billing.')
                        ->send();
                }
            }
        }

        Notification::make()
            ->success()
            ->title('Setup completed')
            ->body('Your business profile is ready.')
            ->send();

        $this->isFinishing = true;
    }

    public function redirectToSubscriptionPage(): void
    {
        $this->redirect(SubscriptionPlans::getUrl(panel: 'user'), navigate: true);
    }

    /**
     * @param  array{name?: string, phone_number?: string, email?: string}  $row
     */
    protected function contactRowHasAnyInput(array $row): bool
    {
        return filled($row['name'] ?? null)
            || filled($row['phone_number'] ?? null)
            || filled($row['email'] ?? null);
    }

    /**
     * @param  array{name?: string, phone_number?: string, email?: string}  $row
     */
    protected function contactRowIsComplete(array $row): bool
    {
        return filled($row['name'] ?? null) && filled($row['phone_number'] ?? null);
    }

    public function validateStep(int $step): void
    {
        match ($step) {
            1 => Validator::make(
                [
                    'business_name' => $this->business_name,
                    'business_description' => $this->business_description,
                ],
                [
                    'business_name' => ['required', 'string', 'max:255'],
                    'business_description' => ['required', 'string', 'max:1000'],
                ],
            )->validate(),
            2 => Validator::make(
                ['business_category' => $this->business_category],
                [
                    'business_category' => ['required', 'string', Rule::in(array_keys($this->getBusinessCategoryOptions()))],
                ],
            )->validate(),
            3 => $this->validateContactRows(),
            default => null,
        };
    }

    protected function validateContactRows(): void
    {
        foreach ($this->contactRows as $index => $row) {
            if (! $this->contactRowHasAnyInput($row)) {
                continue;
            }

            $validator = Validator::make($row, [
                'name' => ['required', 'string', 'max:255'],
                'phone_number' => ['required', 'string', 'max:32'],
                'email' => ['nullable', 'email', 'max:255'],
            ]);

            if ($validator->fails()) {
                throw ValidationException::withMessages([
                    'contactRows' => 'Row '.($index + 1).': please enter a valid name and phone (and optional email), or clear the row.',
                ]);
            }
        }
    }
}
