<?php

namespace App\Providers;

use App\Services\Sms\Contracts\SmsProviderContract;
use App\Services\Sms\Providers\LogSmsProvider;
use App\Services\Sms\Providers\SkySmsProvider;
use App\Services\Sms\Providers\UniSmsProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SmsProviderContract::class, function ($app): SmsProviderContract {
            if ($app->runningUnitTests()) {
                return $app->make(LogSmsProvider::class);
            }

            return match (config('sms.driver')) {
                'unisms' => $app->make(UniSmsProvider::class),
                'skysms' => $app->make(SkySmsProvider::class),
                default => $app->make(LogSmsProvider::class),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::forceScheme('https');
    }
}
