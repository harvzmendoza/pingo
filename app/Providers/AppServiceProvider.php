<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use App\Services\Sms\Contracts\SmsProviderContract;
use App\Services\Sms\Providers\LogSmsProvider;
use App\Services\Sms\Providers\SkySmsProvider;
use App\Services\Sms\Providers\UniSmsProvider;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Provider;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ServiceProvider;

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
        User::observe(UserObserver::class);

        CreateRecord::disableCreateAnother();

        FilamentView::registerRenderHook(
            PanelsRenderHook::AUTH_REGISTER_FORM_AFTER,
            static function (): ?string {
                $panel = Filament::getCurrentPanel();

                if (! $panel?->hasPlugin('filament-socialite')) {
                    return null;
                }

                /** @var FilamentSocialitePlugin $plugin */
                $plugin = $panel->getPlugin('filament-socialite');

                return view('filament-socialite::components.buttons', [
                    'providers' => $providers = $plugin->getProviders(),
                    'visibleProviders' => array_filter($providers, fn (Provider $provider): bool => $provider->isVisible()),
                    'socialiteRoute' => $plugin->getRoute(),
                    'messageBag' => new MessageBag,
                    'showDivider' => $plugin->getShowDivider(),
                ])->render();
            },
        );
    }
}
