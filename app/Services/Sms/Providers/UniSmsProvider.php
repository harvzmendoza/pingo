<?php

namespace App\Services\Sms\Providers;

use App\Services\Sms\Contracts\SmsProviderContract;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

/**
 * UniSMS (Philippines) — https://unismsapi.com/docs/sms
 */
class UniSmsProvider implements SmsProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function send(string $phoneNumber, string $message): array
    {
        $apiKey = config('sms.unisms.api_key');
        $url = rtrim((string) config('sms.unisms.api_url', 'https://unismsapi.com/api/sms'), '/');

        if (blank($apiKey)) {
            throw new InvalidArgumentException('UniSMS API key is not configured. Set UNISMS_API_KEY in your environment.');
        }

        $payload = [
            'recipient' => $phoneNumber,
            'content' => $message,
        ];

        $senderId = config('sms.unisms.sender_id');
        if (filled($senderId)) {
            $payload['sender_id'] = $senderId;
        }

        try {
            $response = Http::withBasicAuth($apiKey, '')
                ->timeout(30)
                ->connectTimeout(10)
                ->acceptJson()
                ->asJson()
                ->post($url, $payload);
        } catch (Throwable $e) {
            Log::error('UniSMS HTTP request failed', [
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }

        $rawBody = $response->body();

        if (! $response->successful()) {
            Log::warning('UniSMS API error response', [
                'status' => $response->status(),
                'body' => $rawBody,
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'response' => $rawBody,
            ];
        }

        $json = $response->json();
        $referenceId = data_get($json, 'message.reference_id');
        $status = (string) data_get($json, 'message.status', '');
        $failReason = data_get($json, 'message.fail_reason');

        $accepted = in_array($status, ['sent', 'pending', 'retrying'], true);

        if (! $accepted || filled($failReason)) {
            return [
                'success' => false,
                'message_id' => is_string($referenceId) ? $referenceId : null,
                'response' => $rawBody,
            ];
        }

        return [
            'success' => true,
            'message_id' => is_string($referenceId) ? $referenceId : null,
            'response' => $rawBody,
        ];
    }
}
