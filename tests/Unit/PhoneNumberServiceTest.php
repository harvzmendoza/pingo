<?php

namespace Tests\Unit;

use App\Services\PhoneNumberService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PhoneNumberServiceTest extends TestCase
{
    #[DataProvider('philippineNumbers')]
    public function test_it_normalizes_philippine_mobile_numbers(string $input, string $expected): void
    {
        $service = new PhoneNumberService;

        $this->assertSame($expected, $service->normalize($input));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function philippineNumbers(): array
    {
        return [
            'leading 09' => ['09171234567', '+639171234567'],
            '63 prefix' => ['639171234567', '+639171234567'],
            'e164' => ['+639171234567', '+639171234567'],
            '10 digits mobile' => ['9171234567', '+639171234567'],
            'with spaces' => ['0917 123 4567', '+639171234567'],
        ];
    }
}
