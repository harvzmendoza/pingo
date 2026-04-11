<?php

namespace App\Jobs;

use App\Exceptions\SmsLimitReachedException;
use App\Models\Contact;
use App\Models\Message;
use App\Services\MessageDispatchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendScheduledCampaignJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<int>  $contactIds
     */
    public function __construct(
        public int $messageId,
        public array $contactIds,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(MessageDispatchService $messageDispatchService): void
    {
        $message = Message::query()->find($this->messageId);

        if (! $message instanceof Message) {
            return;
        }

        $contactIds = collect($this->contactIds)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter()
            ->values();

        if ($contactIds->isEmpty()) {
            $contactIds = Contact::query()
                ->where('user_id', $message->user_id)
                ->pluck('id');
        }

        try {
            $messageDispatchService->sendToContacts($message, $contactIds);
        } catch (SmsLimitReachedException) {
            return;
        }
    }
}
