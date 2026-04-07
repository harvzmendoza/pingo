<?php

namespace Tests\Feature;

use App\Enums\MessageLogStatus;
use App\Models\Contact;
use App\Models\Message;
use App\Models\User;
use App\Services\MessageDispatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageDispatchServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_sent_logs_for_owned_contacts(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
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
}
