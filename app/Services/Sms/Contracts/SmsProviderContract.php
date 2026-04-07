<?php

namespace App\Services\Sms\Contracts;

interface SmsProviderContract
{
    /**
     * Send an SMS via the underlying provider.
     *
     * @return array{success: bool, message_id: string|null, response: string|null}
     */
    public function send(string $phoneNumber, string $message): array;
}
