@php
    use App\Services\SubscriptionService;
    use Filament\Facades\Filament;
    use Filament\Support\Icons\Heroicon;

    $user = Filament::auth()->user();
    $subscriptionService = $user ? app(SubscriptionService::class) : null;
    $subscription = $user && $subscriptionService ? $subscriptionService->getCurrentSubscription($user) : null;
    $planName = $subscription?->plan?->name ?? 'No plan';
    $smsUsed = $subscription?->sms_used ?? 0;
    $smsLimit = $subscription?->plan?->sms_limit ?? 0;
    $remaining = $smsLimit > 0 ? max($smsLimit - $smsUsed, 0) : 0;
    $usagePercent = $smsLimit > 0 ? min(100, round(($smsUsed / $smsLimit) * 100)) : 0;
    $canStartFreeTrial = $user && $subscriptionService
        && ! $subscriptionService->userHasUsedFreePlan($user)
        && (! $subscription || $subscription->plan?->isFree());
    $ctaLabel = $canStartFreeTrial ? 'Start Free Trial' : 'Upgrade plan';
    $subscriptionPlansUrl = \App\Filament\User\Pages\User\SubscriptionPlans::getUrl(panel: 'user');
@endphp

@if ($user)
    <div class="sidebar-user-footer">
        <div class="sidebar-subscription-card">
            <div class="sidebar-subscription-kicker">
                <span class="sidebar-subscription-kicker-dot" aria-hidden="true"></span>
                <span>Subscription</span>
            </div>

            <div class="sidebar-subscription-title-row">
                <span class="sidebar-subscription-plan">{{ $planName }}</span>
            </div>

            <div class="sidebar-subscription-meta">
                @if ($smsLimit > 0)
                    <div class="flex items-center justify-between gap-2 font-medium text-gray-800">
                        <span>SMS today</span>
                        <span class="tabular-nums text-violet-700">{{ number_format($smsUsed) }} / {{ number_format($smsLimit) }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-2 text-gray-500">
                        <span>Remaining</span>
                        <span class="tabular-nums font-semibold text-gray-700">{{ number_format($remaining) }}</span>
                    </div>
                    <div class="sidebar-subscription-progress" role="progressbar" aria-valuenow="{{ $usagePercent }}" aria-valuemin="0" aria-valuemax="100" aria-label="SMS usage today">
                        <div class="sidebar-subscription-progress-bar" style="width: {{ $usagePercent }}%"></div>
                    </div>
                @elseif ($canStartFreeTrial)
                    <p class="text-sm font-medium leading-snug text-gray-700">
                        Welcome — start your one-time trial to send SMS.
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ SubscriptionService::FREE_TRIAL_DAYS }}-day trial · daily allowance auto-renews
                    </p>
                @else
                    <p class="text-xs text-gray-600">
                        Pick a plan to unlock daily SMS limits.
                    </p>
                @endif
            </div>

            <p class="sidebar-subscription-hint">
                Daily limit resets at midnight ({{ config('app.timezone') }}).
            </p>

            @if ($subscription?->ends_at && $subscription->plan?->isFree())
                <div class="sidebar-subscription-trial">
                    Trial ends {{ $subscription->ends_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}
                </div>
            @endif

            <a href="{{ $subscriptionPlansUrl }}" class="sidebar-subscription-cta">
                @if ($canStartFreeTrial)
                    <x-filament::icon :icon="Heroicon::OutlinedSparkles" class="h-3.5 w-3.5 shrink-0 opacity-95" />
                @else
                    <x-filament::icon :icon="Heroicon::OutlinedArrowTrendingUp" class="h-3.5 w-3.5 shrink-0 opacity-95" />
                @endif
                <span>{{ $ctaLabel }}</span>
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
