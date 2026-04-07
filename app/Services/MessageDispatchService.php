<?php

namespace App\Services;

use App\Enums\MessageLogStatus;
use App\Models\Contact;
use App\Models\Message;
use App\Models\MessageLog;
use Illuminate\Support\Collection;

class MessageDispatchService
{
    public function __construct(
        private readonly SmsService $smsService,
    ) {}

    /**
     * @param  array<int>|Collection<int, int>  $contactIds
     * @return array{sent: int, failed: int, skipped: int}
     */
    public function sendToContacts(Message $message, array|Collection $contactIds): array
    {
        $ids = collect($contactIds)->unique()->values();
        $contacts = Contact::query()
            ->where('user_id', $message->user_id)
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $sent = 0;
        $failed = 0;
        $skipped = $ids->count() - $contacts->count();

        foreach ($ids as $id) {
            $contact = $contacts->get($id);
            if (! $contact) {
                continue;
            }

            $result = $this->smsService->send($contact->phone_number, $message->content);
            $success = $result['success'] ?? false;

            MessageLog::query()->create([
                'message_id' => $message->id,
                'contact_id' => $contact->id,
                'status' => $success ? MessageLogStatus::Sent : MessageLogStatus::Failed,
                'response' => $result['response'] ?? null,
                'sent_at' => now(),
            ]);

            if ($success) {
                $sent++;
            } else {
                $failed++;
            }
        }

        return [
            'sent' => $sent,
            'failed' => $failed,
            'skipped' => $skipped,
        ];
    }
}
