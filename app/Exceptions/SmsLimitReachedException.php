<?php

namespace App\Exceptions;

use Exception;

class SmsLimitReachedException extends Exception
{
    public static function withoutActiveSubscription(): self
    {
        return new self('No active subscription found. Submit a subscription request from Billing → Subscription and wait for admin approval before sending SMS.');
    }

    public static function forCurrentPlan(int $smsUsed, int $smsLimit): self
    {
        return new self(
            sprintf(
                'SMS limit reached for your current plan (%d / %d used today). Usage resets daily, or upgrade for a higher daily limit.',
                $smsUsed,
                $smsLimit,
            ),
        );
    }
}
