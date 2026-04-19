<x-filament-panels::page>
    <div class="subscription-page @if ($isRedirectingToSendCampaign) opacity-60 pointer-events-none @endif">
        <header class="subscription-page-hero">
            <div class="subscription-page-hero-inner">
                <p class="subscription-page-hero-kicker">Billing & plans</p>
                <h1 class="subscription-page-hero-title">Choose how you send</h1>
                <p class="subscription-page-hero-text">
                    Start with a one-time free trial or subscribe with payment proof for review. Paid plans require admin approval after you submit your screenshot.
                </p>
            </div>
        </header>

        <div class="subscription-plans-grid">
            @foreach ($this->getPlans() as $plan)
                @php
                    $isFree = (float) $plan->price <= 0;
                @endphp
                <article @class(['subscription-plan-card', 'subscription-plan-card--free' => $isFree])>
                    <div class="subscription-plan-card-inner">
                        <div class="subscription-plan-card-head">
                            <div class="min-w-0 flex-1">
                                <h2 class="subscription-plan-name">{{ $plan->name }}</h2>
                                <p class="subscription-plan-desc">
                                    {{ $plan->description ?: 'Simple SMS plan for your business.' }}
                                </p>
                            </div>
                            <span @class([
                                'subscription-plan-badge',
                                'subscription-plan-badge--free' => $isFree,
                            ])>
                                {{ $isFree ? 'Free trial' : 'Paid' }}
                            </span>
                        </div>

                        <div class="subscription-plan-price-block">
                            <p class="subscription-plan-price">
                                {{ $isFree ? 'Free' : '₱' . number_format((float) $plan->price, 2) }}
                            </p>
                            <p class="subscription-plan-price-note">
                                {{ number_format($plan->sms_limit) }} SMS per day · resets daily
                            </p>
                        </div>

                        @if ($isFree)
                            <button
                                type="button"
                                class="subscription-plan-cta"
                                wire:click="startFreeTrial({{ $plan->id }})"
                                wire:loading.attr="disabled"
                                wire:target="startFreeTrial"
                                @disabled($isRedirectingToSendCampaign)
                            >
                                <span wire:loading.remove wire:target="startFreeTrial">Start free trial</span>
                                <span wire:loading wire:target="startFreeTrial">Starting…</span>
                            </button>
                            <p class="subscription-plan-footnote">
                                One-time {{ \App\Services\SubscriptionService::FREE_TRIAL_DAYS }}-day trial. Your SMS allowance auto-renews each calendar day until the trial ends.
                            </p>
                        @else
                            <button
                                type="button"
                                class="subscription-plan-cta--outline"
                                wire:click="mountAction('requestSubscription', { plan_id: {{ $plan->id }} })"
                            >
                                Subscribe
                            </button>
                            <p class="subscription-plan-footnote">
                                Upload payment proof — we’ll activate your plan after approval.
                            </p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <section class="subscription-section" aria-labelledby="subscription-requests-heading">
            <div class="subscription-section-header">
                <div>
                    <h2 id="subscription-requests-heading" class="subscription-section-title">Your requests</h2>
                    <p class="subscription-section-desc">Track payment submissions waiting for admin review.</p>
                </div>
            </div>
            <div class="subscription-panel">
                <div class="subscription-table-wrap">
                    <table class="subscription-table">
                        <thead>
                            <tr>
                                <th scope="col">Plan</th>
                                <th scope="col">Status</th>
                                <th scope="col">Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->getMyRequests() as $req)
                                <tr>
                                    <td class="font-medium text-gray-900">{{ $req->plan?->name }}</td>
                                    <td>
                                        <span @class([
                                            'subscription-status-pill',
                                            'subscription-status-pill--pending' => $req->status->value === 'pending',
                                            'subscription-status-pill--approved' => $req->status->value === 'approved',
                                            'subscription-status-pill--rejected' => $req->status->value === 'rejected',
                                        ])>
                                            {{ ucfirst($req->status->value) }}
                                        </span>
                                    </td>
                                    <td class="text-gray-600">{{ $req->created_at->format('M j, Y g:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="subscription-table-empty">No requests yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="subscription-section" aria-labelledby="subscription-history-heading">
            <div class="subscription-section-header">
                <div>
                    <h2 id="subscription-history-heading" class="subscription-section-title">Subscription history</h2>
                    <p class="subscription-section-desc">When each plan period started and ended (including your free trial).</p>
                </div>
            </div>
            <div class="subscription-panel">
                <div class="subscription-table-wrap">
                    <table class="subscription-table">
                        <thead>
                            <tr>
                                <th scope="col">Plan</th>
                                <th scope="col">Started</th>
                                <th scope="col">Ended</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->getMyHistory() as $row)
                                <tr>
                                    <td class="font-medium text-gray-900">{{ $row->plan?->name }}</td>
                                    <td class="text-gray-600">{{ $row->started_at->format('M j, Y g:i A') }}</td>
                                    <td class="text-gray-600">{{ $row->ended_at ? $row->ended_at->format('M j, Y g:i A') : '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="subscription-table-empty">
                                        No history yet. After you start a trial or an admin approves a plan, periods appear here.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    @if ($isRedirectingToSendCampaign)
        <div
            class="send-campaign-wizard-finishing"
            x-data
            x-init="setTimeout(() => $wire.redirectToSendCampaign(), 1500)"
        >
            <div class="send-campaign-wizard-finishing-spinner" aria-hidden="true"></div>
            <p class="send-campaign-wizard-finishing-title">Trial activated</p>
            <p class="send-campaign-wizard-finishing-text">Taking you to Send campaign so you can send your first message.</p>
        </div>
    @endif
</x-filament-panels::page>
