<?php

namespace App\Services\Sms\Providers;

use App\Services\Sms\Contracts\SmsProviderContract;
use Illuminate\Support\Facades\Log;

/**
 * UniSMS integration (structure only — wire HTTP client and config when ready).
 */
class UniSmsProvider implements SmsProviderContract
{
    public function __construct()
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function send(string $phoneNumber, string $message): array
    {
        // TODO: POST to config('sms.unisms.api_url') with api_key, sender_id, to, message.
        // Map JSON response to message_id and raw response body.

        Log::debug('UniSMS send not implemented', [
            'to' => $phoneNumber,
            'length' => mb_strlen($message),
        ]);

        return [
            'success' => false,
            'message_id' => null,
            'response' => null,
        ];
    }
}
