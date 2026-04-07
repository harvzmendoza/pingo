<?php

namespace App\Services;

/**
 * Normalizes Philippine mobile numbers to E.164 (+63…).
 */
class PhoneNumberService
{
    /**
     * Examples:
     * - 09171234567 → +639171234567
     * - 639171234567 → +639171234567
     * - +639171234567 → +639171234567
     * - 9171234567 (10 digits, mobile) → +639171234567
     */
    public function normalize(string $number): string
    {
        $trimmed = trim($number);

        if ($trimmed === '') {
            return $trimmed;
        }

        $digitsOnly = preg_replace('/\D+/', '', $trimmed) ?? '';

        if ($digitsOnly === '') {
            return $trimmed;
        }

        if (str_starts_with($trimmed, '+')) {
            if (str_starts_with($digitsOnly, '63')) {
                return '+'.$digitsOnly;
            }

            return $trimmed;
        }

        if (str_starts_with($digitsOnly, '63') && strlen($digitsOnly) >= 11) {
            return '+'.$digitsOnly;
        }

        if (str_starts_with($digitsOnly, '0')) {
            return '+63'.substr($digitsOnly, 1);
        }

        if (strlen($digitsOnly) === 10 && str_starts_with($digitsOnly, '9')) {
            return '+63'.$digitsOnly;
        }

        return $trimmed;
    }
}
