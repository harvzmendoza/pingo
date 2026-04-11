<?php

namespace App\Filament\User\Pages\User;

use App\Models\Plan;
use App\Models\User;
use App\Services\SubscriptionService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
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

    public function subscribe(int $planId, SubscriptionService $subscriptionService): void
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

        $subscriptionService->subscribe($user, $plan);

        Notification::make()
            ->success()
            ->title('Subscription updated')
            ->body("You are now on the {$plan->name} plan.")
            ->send();
    }
}
