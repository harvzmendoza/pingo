<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstantFreeTrialTest extends TestCase
{
    use RefreshDatabase;

    public function test_instant_free_trial_activates_subscription_and_history_once(): void
    {
        $user = User::factory()->create();
        $freePlan = Plan::query()->create([
            'name' => 'Free',
            'price' => 0,
            'sms_limit' => 50,
        ]);

        $service = app(SubscriptionService::class);

        $before = now();
        $this->assertTrue($service->applyInstantFreeTrial($user, $freePlan));
        $user->refresh();
        $endsAt = $user->subscription?->ends_at;
        $this->assertInstanceOf(Carbon::class, $endsAt);
        $expectedSeconds = SubscriptionService::FREE_TRIAL_DAYS * 86400;
        $actualSeconds = $endsAt->getTimestamp() - $before->getTimestamp();
        $this->assertGreaterThanOrEqual($expectedSeconds - 10, $actualSeconds);
        $this->assertLessThanOrEqual($expectedSeconds + 10, $actualSeconds);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'plan_id' => $freePlan->id,
        ]);
        $this->assertDatabaseHas('subscription_histories', [
            'user_id' => $user->id,
            'plan_id' => $freePlan->id,
        ]);

        $this->assertFalse($service->applyInstantFreeTrial($user, $freePlan));
        $this->assertTrue($service->userHasUsedFreePlan($user));
    }

    public function test_instant_free_trial_rejects_non_free_plan(): void
    {
        $user = User::factory()->create();
        $paidPlan = Plan::query()->create([
            'name' => 'Starter',
            'price' => 499,
            'sms_limit' => 200,
        ]);

        $this->assertFalse(app(SubscriptionService::class)->applyInstantFreeTrial($user, $paidPlan));
    }
}
