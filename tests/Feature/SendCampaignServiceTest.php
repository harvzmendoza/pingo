<?php

namespace Tests\Feature;

use App\Enums\MessageType;
use App\Models\Contact;
use App\Models\Plan;
use App\Models\User;
use App\Services\SendCampaignService;
use App\Services\Sms\Contracts\SmsProviderContract;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SendCampaignServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(SmsProviderContract::class, fn (): SmsProviderContract => new class implements SmsProviderContract
        {
            public function send(string $phoneNumber, string $message): array
            {
                return [
                    'success' => true,
                    'message_id' => 'campaign-test-message-id',
                    'response' => '{"status":"sent"}',
                ];
            }
        });
    }

    public function test_it_creates_message_and_sends_to_selected_contacts(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $plan = Plan::query()->create([
            'name' => 'Starter',
            'price' => 499,
            'sms_limit' => 100,
        ]);
        app(SubscriptionService::class)->activateApprovedPlan($user, $plan);

        $contactA = Contact::factory()->for($user)->create();
        $contactB = Contact::factory()->for($user)->create();
        Contact::factory()->for($otherUser)->create();

        $result = app(SendCampaignService::class)->send(
            user: $user,
            content: 'Promo today only!',
            contactIds: [$contactA->id, $contactB->id],
            sendToAllContacts: false,
        );

        $this->assertDatabaseHas('messages', [
            'id' => $result['message']->id,
            'user_id' => $user->id,
            'type' => MessageType::Sms->value,
        ]);
        $this->assertSame(2, $result['sent']);
        $this->assertSame(0, $result['failed']);
        $this->assertSame(0, $result['skipped']);
    }

    public function test_it_can_send_to_all_contacts_of_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $plan = Plan::query()->create([
            'name' => 'Starter',
            'price' => 499,
            'sms_limit' => 100,
        ]);
        app(SubscriptionService::class)->activateApprovedPlan($user, $plan);

        Contact::factory()->count(3)->for($user)->create();
        Contact::factory()->count(2)->for($otherUser)->create();

        $result = app(SendCampaignService::class)->send(
            user: $user,
            content: 'System maintenance notice.',
            contactIds: [],
            sendToAllContacts: true,
        );

        $this->assertSame(3, $result['sent']);
        $this->assertSame(0, $result['failed']);
        $this->assertSame(0, $result['skipped']);
    }
}
