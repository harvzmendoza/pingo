<?php

namespace Tests\Feature;

use App\Services\Sms\Providers\SkySmsProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\TestCase;

class SkySmsProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('sms.skysms.api_key', 'test-api-key');
        Config::set('sms.skysms.base_url', 'https://skysms.skyio.site');
        Config::set('sms.skysms.use_subscription', false);
    }

    public function test_it_posts_to_skysms_and_returns_success(): void
    {
        Http::fake([
            'https://skysms.skyio.site/api/v1/sms/send' => Http::response([
                'success' => true,
                'message' => 'SMS queued successfully',
                'data' => [
                    'id' => 123,
                    'status' => 'pending',
                ],
            ], 200),
        ]);

        $provider = app(SkySmsProvider::class);
        $result = $provider->send('+639123456789', 'Hello');

        $this->assertTrue($result['success']);
        $this->assertSame('123', $result['message_id']);
        $this->assertIsString($result['response']);

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://skysms.skyio.site/api/v1/sms/send'
                && $request->hasHeader('X-API-Key', 'test-api-key')
                && $request->data()['phone_number'] === '+639123456789'
                && $request->data()['message'] === 'Hello'
                && ! array_key_exists('use_subscription', $request->data());
        });
    }

    public function test_policy_warning_is_treated_as_failure(): void
    {
        Http::fake([
            'https://skysms.skyio.site/api/v1/sms/send' => Http::response([
                'success' => true,
                'status' => 'sent',
                'warning' => 'Message contained a URL. A 10-credit penalty has been applied and the message was not delivered.',
                'penalty_credits' => 10,
            ], 200),
        ]);

        $provider = app(SkySmsProvider::class);
        $result = $provider->send('+639123456789', 'http://example.com');

        $this->assertFalse($result['success']);
        $this->assertNull($result['message_id']);
    }

    public function test_it_throws_when_api_key_is_not_configured(): void
    {
        Config::set('sms.skysms.api_key', null);

        Http::fake();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SkySMS API key is not configured');

        app(SkySmsProvider::class)->send('+639123456789', 'Hi');
    }

    public function test_it_sends_use_subscription_when_enabled(): void
    {
        Config::set('sms.skysms.use_subscription', true);

        Http::fake([
            'https://skysms.skyio.site/api/v1/sms/send' => Http::response([
                'success' => true,
                'data' => ['id' => 1, 'status' => 'pending'],
            ], 200),
        ]);

        app(SkySmsProvider::class)->send('+639123456789', 'Hello');

        Http::assertSent(function ($request): bool {
            return ($request->data()['use_subscription'] ?? null) === true;
        });
    }
}
