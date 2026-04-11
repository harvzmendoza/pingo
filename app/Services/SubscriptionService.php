<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\CarbonInterface;

class SubscriptionService
{
    public function getCurrentPlan(User $user): ?Plan
    {
        return $this->getCurrentSubscription($user)?->plan;
    }

    public function canSendSms(User $user): bool
    {
        $subscription = $this->getCurrentSubscription($user);

        if (! $subscription instanceof Subscription) {
            return false;
        }

        return $subscription->sms_used < $subscription->plan->sms_limit;
    }

    public function incrementUsage(User $user, int $count = 1): void
    {
        if ($count <= 0) {
            return;
        }

        $subscription = $this->getCurrentSubscription($user);
        if (! $subscription instanceof Subscription) {
            return;
        }

        $subscription->increment('sms_used', $count);
    }

    public function subscribe(User $user, Plan $plan): Subscription
    {
        $today = $this->today();

        return Subscription::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'plan_id' => $plan->id,
                'sms_used' => 0,
                'sms_usage_date' => $today->toDateString(),
                'starts_at' => now(),
                'ends_at' => null,
            ],
        );
    }

    public function getCurrentSubscription(User $user): ?Subscription
    {
        $subscription = Subscription::query()
            ->with('plan')
            ->whereBelongsTo($user)
            ->active()
            ->first();

        if (! $subscription instanceof Subscription) {
            return null;
        }

        return $this->syncDailySmsUsage($subscription);
    }

    /**
     * When the app-local calendar day changes, reset usage so limits apply per day for the current plan.
     */
    public function syncDailySmsUsage(Subscription $subscription): Subscription
    {
        $today = $this->today();

        if ($subscription->sms_usage_date === null || ! $subscription->sms_usage_date->isSameDay($today)) {
            $subscription->forceFill([
                'sms_used' => 0,
                'sms_usage_date' => $today->toDateString(),
            ])->save();
        }

        return $subscription->refresh();
    }

    private function today(): CarbonInterface
    {
        return now(config('app.timezone'))->startOfDay();
    }
}
