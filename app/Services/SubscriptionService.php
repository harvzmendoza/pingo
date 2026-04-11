<?php

namespace App\Services;

use App\Enums\SubscriptionRequestStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionHistory;
use App\Models\SubscriptionRequest;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    /**
     * One-time free trial length. After this period the subscription expires unless the user upgrades.
     */
    public const FREE_TRIAL_DAYS = 3;

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

    /**
     * Apply an admin-approved plan to the user's active subscription row.
     */
    public function activateApprovedPlan(User $user, Plan $plan, ?Carbon $subscriptionEndsAt = null): Subscription
    {
        $today = $this->today();

        return Subscription::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'plan_id' => $plan->id,
                'sms_used' => 0,
                'sms_usage_date' => $today->toDateString(),
                'starts_at' => now(),
                'ends_at' => $subscriptionEndsAt,
            ],
        );
    }

    /**
     * Expiration instant for the free-trial subscription row ({@see self::FREE_TRIAL_DAYS}).
     */
    public function freeTrialSubscriptionEndsAt(): Carbon
    {
        return now()->addDays(self::FREE_TRIAL_DAYS);
    }

    /**
     * Start the free trial immediately (no admin request). Allowed only once per user (tracked via history).
     */
    public function applyInstantFreeTrial(User $user, Plan $plan): bool
    {
        if (! $plan->isFree()) {
            return false;
        }

        if ($this->userHasUsedFreePlan($user)) {
            return false;
        }

        DB::transaction(function () use ($user, $plan): void {
            SubscriptionHistory::query()
                ->where('user_id', $user->id)
                ->whereNull('ended_at')
                ->update(['ended_at' => now()]);

            $this->activateApprovedPlan(
                user: $user,
                plan: $plan,
                subscriptionEndsAt: $this->freeTrialSubscriptionEndsAt(),
            );

            SubscriptionHistory::query()->create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'subscription_request_id' => null,
                'started_at' => now(),
                'ended_at' => null,
            ]);
        });

        return true;
    }

    public function userHasPendingSubscriptionRequest(User $user): bool
    {
        return SubscriptionRequest::query()
            ->where('user_id', $user->id)
            ->where('status', SubscriptionRequestStatus::Pending)
            ->exists();
    }

    /**
     * Free trial / zero-price plan can only be consumed once per user (by history).
     */
    public function userHasUsedFreePlan(User $user): bool
    {
        $freePlanIds = Plan::query()
            ->where('price', '<=', 0)
            ->pluck('id');

        if ($freePlanIds->isEmpty()) {
            return false;
        }

        return SubscriptionHistory::query()
            ->where('user_id', $user->id)
            ->whereIn('plan_id', $freePlanIds)
            ->exists();
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
     * Auto-renew the daily SMS allowance: when the app-local calendar day changes, reset the sms_used counter.
     * Applies to every active subscription (including the 3-day free trial) until the subscription ends.
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
