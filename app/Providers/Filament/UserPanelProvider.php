<?php

namespace App\Providers\Filament;

use App\Filament\User\Pages\BusinessOnboarding;
use App\Filament\User\Pages\Dashboard;
use App\Filament\User\Pages\MessagesCalendar;
use App\Filament\User\Widgets\CampaignVolumeChart;
use App\Filament\User\Widgets\ContactsGrowthChart;
use App\Filament\User\Widgets\DeliveryStatusSplitChart;
use App\Filament\User\Widgets\DeliveryTrendChart;
use App\Filament\User\Widgets\MessagingStats;
use App\Http\Middleware\EnsureUserBusinessOnboardingIsComplete;
use App\Http\Middleware\EnsureUserHasUserRole;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Provider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Config;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class UserPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('user')
            ->path('user')
            ->viteTheme('resources/css/filament/user/theme.css')
            ->login()
            ->registration()
            ->brandName('User Panel')
            ->darkMode(false)
            ->brandLogo(asset('images/pingo-full.png'))
            ->brandLogoHeight('3rem')
            ->colors([
                'primary' => Color::Purple,
            ])
            ->discoverResources(in: app_path('Filament/User/Resources'), for: 'App\Filament\User\Resources')
            ->discoverPages(in: app_path('Filament/User/Pages'), for: 'App\Filament\User\Pages')
            ->pages([
                Dashboard::class,
                BusinessOnboarding::class,
                MessagesCalendar::class,
            ])
            ->discoverWidgets(in: app_path('Filament/User/Widgets'), for: 'App\Filament\User\Widgets')
            ->widgets([
                MessagingStats::class,
                DeliveryTrendChart::class,
                CampaignVolumeChart::class,
                ContactsGrowthChart::class,
                DeliveryStatusSplitChart::class,
            ])
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn (): string => view('filament.user.topbar.send-message-button')->render(),
            )
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): string => view('filament.user.topbar.tutorial-help-trigger')->render(),
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                fn (): string => view('filament.user.sidebar.footer')->render(),
            )
            ->plugins([
                FilamentSocialitePlugin::make()
                    ->providers([
                        Provider::make('google')
                            ->label('Google')
                            ->icon('heroicon-o-globe-alt')
                            ->outlined(false)
                            ->visible(fn (): bool => filled(Config::string('services.google.client_id'))),
                        Provider::make('github')
                            ->label('GitHub')
                            ->icon('heroicon-o-code-bracket')
                            ->outlined(false)
                            ->visible(fn (): bool => filled(Config::string('services.github.client_id'))),
                    ])
                    ->registration(true),
            ])
            ->renderHook(
                PanelsRenderHook::SIMPLE_LAYOUT_START,
                fn (): string => request()->routeIs('filament.user.pages.business-onboarding')
                    ? view('filament.user.layout.onboarding-simple-brand')->render()
                    : '',
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureUserHasUserRole::class,
                EnsureUserBusinessOnboardingIsComplete::class,
            ], isPersistent: true);
    }
}
