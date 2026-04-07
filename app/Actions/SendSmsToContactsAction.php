<?php

namespace App\Actions;

use App\Models\Message;
use App\Services\MessageDispatchService;

/**
 * Thin orchestration entry for sending an existing message to contacts (UI, jobs, etc.).
 */
final readonly class SendSmsToContactsAction
{
    public function __construct(
        private MessageDispatchService $messageDispatchService,
    ) {}

    /**
     * @param  array<int>  $contactIds
     * @return array{sent: int, failed: int, skipped: int}
     */
    public function execute(Message $message, array $contactIds): array
    {
        return $this->messageDispatchService->sendToContacts($message, $contactIds);
    }
}
