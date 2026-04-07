<?php

namespace App\Services;

use App\Services\Sms\Contracts\SmsProviderContract;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Application entry point for outbound SMS. Delegates to a configured provider.
 */
class SmsService
{
    public function __construct(
        private readonly SmsProviderContract $provider,
    ) {}

    /**
     * @return array{
     *     success: bool,
     *     message_id: string|null,
     *     response: string|null,
     *     error_message: string|null,
     * }
     */
    public function send(string $phoneNumber, string $message): array
    {
        try {
            $result = $this->provider->send($phoneNumber, $message);

            $success = (bool) ($result['success'] ?? false);

            if (! $success) {
                return [
                    'success' => false,
                    'message_id' => $result['message_id'] ?? null,
                    'response' => $result['response'] ?? null,
                    'error_message' => $result['response'] ?? 'The SMS provider did not accept the message.',
                ];
            }

            return [
                'success' => true,
                'message_id' => $result['message_id'] ?? null,
                'response' => $result['response'] ?? null,
                'error_message' => null,
            ];
        } catch (Throwable $e) {
            Log::error('SMS provider failed', [
                'to' => $phoneNumber,
                'exception' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'response' => null,
                'error_message' => $e->getMessage(),
            ];
        }
    }
}
