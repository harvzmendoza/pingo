<?php

namespace App\Services;

use App\Enums\MessageLogStatus;
use App\Exceptions\SmsLimitReachedException;
use App\Models\Contact;
use App\Models\Message;
use App\Models\MessageLog;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Throwable;

class MessageDispatchService
{
    public function __construct(
        private readonly SmsService $smsService,
        private readonly PhoneNumberService $phoneNumberService,
        private readonly SubscriptionService $subscriptionService,
    ) {}

    /**
     * @param  array<int>|Collection<int, int>  $contactIds
     * @return array{sent: int, failed: int, skipped: int}
     */
    public function sendToContacts(Message $message, array|Collection $contactIds): array
    {
        $user = User::query()->find($message->user_id);
        if (! $user instanceof User) {
            return ['sent' => 0, 'failed' => 0, 'skipped' => 0];
        }

        $subscription = $this->subscriptionService->getCurrentSubscription($user);
        if (! $subscription) {
            throw SmsLimitReachedException::withoutActiveSubscription();
        }

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
            if (! $this->subscriptionService->canSendSms($user)) {
                throw SmsLimitReachedException::forCurrentPlan(
                    $subscription->sms_used,
                    $subscription->plan->sms_limit,
                );
            }

            $contact = $contacts->get($id);
            if (! $contact) {
                continue;
            }

            try {
                $normalizedPhone = $this->phoneNumberService->normalize($contact->phone_number);
                $result = $this->smsService->send($normalizedPhone, $message->content);
            } catch (Throwable $e) {
                $this->persistLog(
                    message: $message,
                    contact: $contact,
                    status: MessageLogStatus::Failed,
                    response: null,
                    providerMessageId: null,
                    errorMessage: $e->getMessage(),
                    sentAt: null,
                );
                $failed++;

                continue;
            }

            $success = $result['success'] ?? false;

            $this->persistLog(
                message: $message,
                contact: $contact,
                status: $success ? MessageLogStatus::Sent : MessageLogStatus::Failed,
                response: $result['response'] ?? null,
                providerMessageId: $result['message_id'] ?? null,
                errorMessage: $success ? null : ($result['error_message'] ?? null),
                sentAt: $success ? now() : null,
            );

            if ($success) {
                $this->subscriptionService->incrementUsage($user);
                $subscription->refresh();
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

    private function persistLog(
        Message $message,
        Contact $contact,
        MessageLogStatus $status,
        ?string $response,
        ?string $providerMessageId,
        ?string $errorMessage,
        ?Carbon $sentAt,
    ): void {
        MessageLog::query()->updateOrCreate([
            'message_id' => $message->id,
            'contact_id' => $contact->id,
        ], [
            'status' => $status,
            'response' => $response,
            'provider_message_id' => $providerMessageId,
            'error_message' => $errorMessage,
            'sent_at' => $sentAt,
        ]);
    }
}
