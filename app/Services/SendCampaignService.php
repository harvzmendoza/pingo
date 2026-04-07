<?php

namespace App\Services;

use App\Enums\MessageType;
use App\Models\Contact;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Collection;

class SendCampaignService
{
    public function __construct(
        private readonly MessageDispatchService $messageDispatchService,
    ) {}

    /**
     * @param  array<int>|Collection<int, int>  $contactIds
     * @return array{message: Message, sent: int, failed: int, skipped: int}
     */
    public function send(User $user, string $content, array|Collection $contactIds, bool $sendToAllContacts = false): array
    {
        $ids = $sendToAllContacts
            ? Contact::query()->where('user_id', $user->id)->pluck('id')
            : collect($contactIds)->map(fn (mixed $id): int => (int) $id)->filter()->values();

        $message = Message::query()->create([
            'user_id' => $user->id,
            'content' => $content,
            'type' => MessageType::Sms,
        ]);

        $result = $this->messageDispatchService->sendToContacts($message, $ids);

        return [
            'message' => $message,
            'sent' => $result['sent'],
            'failed' => $result['failed'],
            'skipped' => $result['skipped'],
        ];
    }
}
