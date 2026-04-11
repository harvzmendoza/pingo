<?php

namespace Tests\Feature;

use App\Enums\MessageLogStatus;
use App\Exceptions\SmsLimitReachedException;
use App\Models\Contact;
use App\Models\Message;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\MessageDispatchService;
use App\Services\Sms\Contracts\SmsProviderContract;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MessageDispatchServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(SmsProviderContract::class, fn (): SmsProviderContract => new class implements SmsProviderContract
        {
            public function send(string $phoneNumber, string $message): array
            {
                return [
                    'success' => true,
                    'message_id' => 'test-message-id',
                    'response' => '{"status":"sent"}',
                ];
            }
        });
    }

    public function test_it_creates_sent_logs_for_owned_contacts(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $plan = Plan::query()->create([
            'name' => 'Starter',
            'price' => 499,
            'sms_limit' => 10,
        ]);
        app(SubscriptionService::class)->activateApprovedPlan($user, $plan);
        $contact = Contact::factory()->for($user)->create();
        Contact::factory()->for($otherUser)->create();

        $message = Message::factory()->for($user)->create();

        $service = app(MessageDispatchService::class);
        $result = $service->sendToContacts($message, [$contact->id, 999_999]);

        $this->assertSame(1, $result['sent']);
        $this->assertSame(0, $result['failed']);
        $this->assertSame(1, $result['skipped']);

        $this->assertDatabaseHas('message_logs', [
            'message_id' => $message->id,
            'contact_id' => $contact->id,
            'status' => MessageLogStatus::Sent->value,
        ]);
    }

    public function test_it_stops_sending_when_sms_limit_is_reached(): void
    {
        $user = User::factory()->create();
        $plan = Plan::query()->create([
            'name' => 'Starter',
            'price' => 499,
            'sms_limit' => 1,
        ]);
        Subscription::query()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'sms_used' => 1,
            'sms_usage_date' => now()->toDateString(),
            'starts_at' => now(),
            'ends_at' => null,
        ]);

        $contact = Contact::factory()->for($user)->create();
        $message = Message::factory()->for($user)->create();

        $service = app(MessageDispatchService::class);

        $this->expectException(SmsLimitReachedException::class);

        $service->sendToContacts($message, [$contact->id]);
    }

    public function test_it_resets_sms_used_when_calendar_day_changes(): void
    {
        $this->travelTo(Carbon::parse('2026-01-10 12:00:00', config('app.timezone')));

        $user = User::factory()->create();
        $plan = Plan::query()->create([
            'name' => 'Starter',
            'price' => 499,
            'sms_limit' => 2,
        ]);
        Subscription::query()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'sms_used' => 2,
            'sms_usage_date' => '2026-01-09',
            'starts_at' => now(),
            'ends_at' => null,
        ]);

        $service = app(SubscriptionService::class);
        $subscription = $service->getCurrentSubscription($user);

        $this->assertSame(0, $subscription->sms_used);
        $this->assertTrue($subscription->sms_usage_date->isSameDay(now()));
        $this->assertTrue($service->canSendSms($user));
    }
}
