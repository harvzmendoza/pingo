<?php

namespace App\Filament\User\Pages\User;

use App\Enums\SubscriptionRequestStatus;
use App\Models\Plan;
use App\Models\SubscriptionHistory;
use App\Models\SubscriptionRequest;
use App\Models\User;
use App\Services\SubscriptionService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use UnitEnum;

class SubscriptionPlans extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 1;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Subscription';

    protected static ?string $title = 'Subscription Plans';

    protected string $view = 'filament.user.pages.user.subscription-plans';

    /**
     * @return Collection<int, Plan>
     */
    public function getPlans(): Collection
    {
        return Plan::query()->orderBy('price')->get();
    }

    /**
     * @return Collection<int, SubscriptionRequest>
     */
    public function getMyRequests(): Collection
    {
        /** @var User|null $user */
        $user = Filament::auth()->user();
        if (! $user instanceof User) {
            return collect();
        }

        return SubscriptionRequest::query()
            ->where('user_id', $user->id)
            ->with('plan')
            ->latest()
            ->limit(25)
            ->get();
    }

    /**
     * @return EloquentCollection<int, SubscriptionHistory>
     */
    public function getMyHistory(): EloquentCollection
    {
        /** @var User|null $user */
        $user = Filament::auth()->user();
        if (! $user instanceof User) {
            return collect();
        }

        return $user->subscriptionHistories()
            ->with('plan')
            ->latest('started_at')
            ->limit(25)
            ->get();
    }

    public function startFreeTrial(int $planId, SubscriptionService $subscriptionService): void
    {
        /** @var User|null $user */
        $user = Filament::auth()->user();
        if (! $user instanceof User) {
            return;
        }

        $plan = Plan::query()->find($planId);
        if (! $plan instanceof Plan) {
            return;
        }

        if (! $plan->isFree()) {
            Notification::make()
                ->warning()
                ->title('Not a free plan')
                ->body('Use Subscribe on paid plans to submit a payment request.')
                ->send();

            return;
        }

        if ($subscriptionService->userHasUsedFreePlan($user)) {
            Notification::make()
                ->danger()
                ->title('Free trial already used')
                ->body('The free trial can only be used once per account.')
                ->send();

            return;
        }

        if (! $subscriptionService->applyInstantFreeTrial($user, $plan)) {
            Notification::make()
                ->danger()
                ->title('Unable to start trial')
                ->body('Please try again or contact support.')
                ->send();

            return;
        }

        Notification::make()
            ->success()
            ->title('Free trial started')
            ->body('Your one-time trial runs for '.SubscriptionService::FREE_TRIAL_DAYS.' days. Your SMS allowance renews automatically at the start of each calendar day ('.config('app.timezone').') until the trial ends.')
            ->send();
    }

    public function requestSubscriptionAction(): Action
    {
        return Action::make('requestSubscription')
            ->label('Subscribe')
            ->modalHeading('Request subscription')
            ->modalDescription('Submit your payment proof for review. An admin will activate your plan after approval.')
            ->modalSubmitActionLabel('Submit request')
            ->fillForm(fn (array $arguments): array => [
                'plan_id' => $arguments['plan_id'] ?? null,
            ])
            ->schema([
                Hidden::make('plan_id')
                    ->required(),
                TextInput::make('payer_name')
                    ->label('Payer name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('payment_reference')
                    ->label('Payment reference / transaction ID')
                    ->maxLength(255),
                Textarea::make('notes')
                    ->label('Additional details')
                    ->rows(3)
                    ->maxLength(2000),
                FileUpload::make('payment_screenshot')
                    ->label('Payment screenshot')
                    ->image()
                    ->disk('public')
                    ->directory(fn (): string => 'subscription-payments/'.Filament::auth()->id())
                    ->visibility('public')
                    ->required(),
            ])
            ->action(function (array $data, SubscriptionService $subscriptionService): void {
                /** @var User|null $user */
                $user = Filament::auth()->user();
                if (! $user instanceof User) {
                    return;
                }

                $plan = $this->planFromId($data['plan_id'] ?? null);
                if (! $plan instanceof Plan) {
                    return;
                }

                if ($plan->isFree()) {
                    Notification::make()
                        ->warning()
                        ->title('Free trial')
                        ->body('Use Start free trial on the Free plan card — no payment request is needed.')
                        ->send();

                    return;
                }

                if ($subscriptionService->userHasPendingSubscriptionRequest($user)) {
                    Notification::make()
                        ->warning()
                        ->title('Request already pending')
                        ->body('You already have a subscription request waiting for review.')
                        ->send();

                    return;
                }

                $path = $data['payment_screenshot'] ?? null;

                if (empty($path)) {
                    Notification::make()
                        ->danger()
                        ->title('Screenshot required')
                        ->body('Please upload a payment screenshot for paid plans.')
                        ->send();

                    return;
                }

                SubscriptionRequest::query()->create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'payment_screenshot_path' => is_array($path) ? ($path[0] ?? null) : $path,
                    'payer_name' => $data['payer_name'] ?? null,
                    'payment_reference' => $data['payment_reference'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'status' => SubscriptionRequestStatus::Pending,
                ]);

                Notification::make()
                    ->success()
                    ->title('Request submitted')
                    ->body('We will review your payment and activate your plan when approved.')
                    ->send();
            });
    }

    private function planFromId(mixed $planId): ?Plan
    {
        if ($planId === null || $planId === '') {
            return null;
        }

        return Plan::query()->find((int) $planId);
    }
}
