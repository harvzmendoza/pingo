<?php

namespace App\Services\Sms\Providers;

use App\Services\Sms\Contracts\SmsProviderContract;
use Illuminate\Support\Facades\Log;

/**
 * Local / test provider: logs the payload and reports success without an external API.
 */
class LogSmsProvider implements SmsProviderContract
{
    public function send(string $phoneNumber, string $message): array
    {
        Log::info('SMS send (log driver)', [
            'to' => $phoneNumber,
            'length' => mb_strlen($message),
        ]);

        $payload = [
            'simulated' => true,
            'to' => $phoneNumber,
        ];

        return [
            'success' => true,
            'message_id' => null,
            'response' => json_encode($payload, JSON_THROW_ON_ERROR),
        ];
    }
}
