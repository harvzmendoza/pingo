<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send an SMS to the given number. Replace this implementation with a real provider later.
     *
     * @return array{success: bool, response: string|null}
     */
    public function send(string $phoneNumber, string $message): array
    {
        Log::info('SMS simulated send', [
            'to' => $phoneNumber,
            'length' => mb_strlen($message),
        ]);

        return [
            'success' => true,
            'response' => json_encode([
                'simulated' => true,
                'to' => $phoneNumber,
            ], JSON_THROW_ON_ERROR),
        ];
    }
}
