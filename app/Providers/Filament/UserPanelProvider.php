<?php

namespace App\Providers\Filament;

use App\Filament\User\Pages\BusinessOnboarding;
use App\Filament\User\Widgets\CampaignVolumeChart;
use App\Filament\User\Widgets\ContactsGrowthChart;
use App\Filament\User\Widgets\DeliveryStatusSplitChart;
use App\Filament\User\Widgets\DeliveryTrendChart;
use App\Filament\User\Widgets\MessagingStats;
use App\Http\Middleware\EnsureUserBusinessOnboardingIsComplete;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class UserPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('user')
            ->path('user')
            ->login()
            // ->registration()
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
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): string => view('filament.user.topbar.tutorial-help-trigger')->render(),
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
                EnsureUserBusinessOnboardingIsComplete::class,
            ], isPersistent: true);
    }
}
