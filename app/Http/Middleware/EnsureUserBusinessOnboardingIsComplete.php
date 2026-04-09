<?php

namespace App\Http\Middleware;

use App\Filament\User\Pages\BusinessOnboarding;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBusinessOnboardingIsComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        if (
            $user->hasCompletedBusinessOnboarding() ||
            $request->routeIs('filament.user.pages.business-onboarding') ||
            $request->routeIs('filament.user.auth.logout')
        ) {
            return $next($request);
        }

        return redirect(BusinessOnboarding::getUrl(panel: 'user'));
    }
}
