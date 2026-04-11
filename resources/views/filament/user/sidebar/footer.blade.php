@php
    use App\Services\SubscriptionService;
    use Filament\Facades\Filament;
    use Filament\Support\Icons\Heroicon;

    $user = Filament::auth()->user();
    $subscription = $user ? app(SubscriptionService::class)->getCurrentSubscription($user) : null;
    $planName = $subscription?->plan?->name ?? 'No plan';
    $smsUsed = $subscription?->sms_used ?? 0;
    $smsLimit = $subscription?->plan?->sms_limit ?? 0;
    $remaining = max($smsLimit - $smsUsed, 0);
@endphp

@if ($user)
    <div class="sidebar-user-footer">
        <div class="rounded-lg border border-purple-100 bg-purple-50/60 p-3">
            <div class="text-[11px] font-semibold uppercase tracking-wide text-purple-700">Subscription</div>
            <div class="mt-1 text-sm font-semibold text-gray-900">{{ $planName }}</div>
            <div class="mt-1 text-xs text-gray-600">{{ number_format($smsUsed) }} / {{ number_format($smsLimit) }} SMS today</div>
            <div class="mt-1 text-xs text-gray-600">{{ number_format($remaining) }} remaining today</div>
            <a href="{{ \App\Filament\User\Pages\User\SubscriptionPlans::getUrl(panel: 'user') }}" class="mt-2 inline-flex text-xs font-semibold text-purple-700 hover:text-purple-900">
                Upgrade plan
            </a>
        </div>

        <div class="sidebar-user-row">
            <div class="sidebar-user-meta">
                <div class="sidebar-user-label">Account</div>
                <div class="sidebar-user-name">
                    {{ filament()->getUserName($user) }}
                </div>
                <div class="sidebar-user-subtitle">Signed in</div>
            </div>

            <form method="POST" action="{{ Filament::getLogoutUrl() }}">
                @csrf

                <button type="submit" class="sidebar-signout-btn" aria-label="Sign out" title="Sign out">
                    <x-filament::icon :icon="Heroicon::ArrowLeftEndOnRectangle" class="h-4 w-4" />
                </button>
            </form>
        </div>
    </div>
@endif
