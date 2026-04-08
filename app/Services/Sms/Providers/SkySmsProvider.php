<?php

namespace App\Services\Sms\Providers;

use App\Services\Sms\Contracts\SmsProviderContract;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

/**
 * SkySMS — https://skysms.skyio.site/docs
 */
class SkySmsProvider implements SmsProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function send(string $phoneNumber, string $message): array
    {
        $apiKey = config('sms.skysms.api_key');
        $baseUrl = rtrim((string) config('sms.skysms.base_url', 'https://skysms.skyio.site'), '/');
        $url = $baseUrl.'/api/v1/sms/send';

        if (blank($apiKey)) {
            throw new InvalidArgumentException('SkySMS API key is not configured. Set SKYSMS_API_KEY in your environment.');
        }

        $payload = [
            'phone_number' => $phoneNumber,
            'message' => $message,
        ];

        if (config('sms.skysms.use_subscription')) {
            $payload['use_subscription'] = true;
        }

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
            ])
                ->timeout(30)
                ->connectTimeout(10)
                ->acceptJson()
                ->asJson()
                ->post($url, $payload);
        } catch (Throwable $e) {
            Log::error('SkySMS HTTP request failed', [
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }

        $rawBody = $response->body();

        if (! $response->successful()) {
            Log::warning('SkySMS API error response', [
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

        if (! data_get($json, 'success', false)) {
            return [
                'success' => false,
                'message_id' => null,
                'response' => $rawBody,
            ];
        }

        /** Policy violations: API may return success with a warning and no delivery. */
        if (filled(data_get($json, 'warning'))) {
            return [
                'success' => false,
                'message_id' => null,
                'response' => $rawBody,
            ];
        }

        $id = data_get($json, 'data.id');
        $status = (string) data_get($json, 'data.status', '');

        if ($status === 'failed') {
            return [
                'success' => false,
                'message_id' => $id !== null ? (string) $id : null,
                'response' => $rawBody,
            ];
        }

        return [
            'success' => true,
            'message_id' => $id !== null ? (string) $id : null,
            'response' => $rawBody,
        ];
    }
}
