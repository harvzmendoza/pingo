<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-3">
        @foreach ($this->getPlans() as $plan)
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $plan->name }}</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ $plan->description ?: 'Simple SMS plan for your business.' }}
                        </p>
                    </div>
                    <div class="rounded-full bg-purple-50 px-3 py-1 text-xs font-semibold text-purple-700">
                        {{ number_format((float) $plan->price, 2) === '0.00' ? 'Free Trial' : 'Paid' }}
                    </div>
                </div>

                <div class="mt-5">
                    <p class="text-2xl font-bold text-gray-900">
                        {{ (float) $plan->price <= 0 ? 'Free' : '₱' . number_format((float) $plan->price, 2) }}
                    </p>
                    <p class="mt-1 text-sm text-gray-600">{{ number_format($plan->sms_limit) }} SMS per day</p>
                </div>

                @if ((float) $plan->price <= 0)
                    <x-filament::button
                        class="mt-5 w-full"
                        color="primary"
                        wire:click="startFreeTrial({{ $plan->id }})"
                    >
                        Start free trial
                    </x-filament::button>
                    <p class="mt-2 text-center text-xs text-gray-500">
                        One-time offer: {{ \App\Services\SubscriptionService::FREE_TRIAL_DAYS }} days. SMS allowance auto-renews each calendar day until the trial ends.
                    </p>
                @else
                    <x-filament::button
                        class="mt-5 w-full"
                        color="primary"
                        wire:click="mountAction('requestSubscription', { plan_id: {{ $plan->id }} })"
                    >
                        Subscribe
                    </x-filament::button>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-10 space-y-2">
        <h2 class="text-lg font-semibold text-gray-900">Your requests</h2>
        <p class="text-sm text-gray-600">Payment submissions waiting for admin approval.</p>
        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Plan</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Submitted</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($this->getMyRequests() as $req)
                        <tr>
                            <td class="px-4 py-3 text-gray-900">{{ $req->plan?->name }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'inline-flex rounded-full px-2 py-0.5 text-xs font-semibold',
                                    'bg-amber-50 text-amber-800' => $req->status->value === 'pending',
                                    'bg-emerald-50 text-emerald-800' => $req->status->value === 'approved',
                                    'bg-rose-50 text-rose-800' => $req->status->value === 'rejected',
                                ])>
                                    {{ ucfirst($req->status->value) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $req->created_at->format('M j, Y g:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-gray-500">No requests yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-10 space-y-2">
        <h2 class="text-lg font-semibold text-gray-900">Subscription history</h2>
        <p class="text-sm text-gray-600">When each plan period started and ended.</p>
        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Plan</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Started</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Ended</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($this->getMyHistory() as $row)
                        <tr>
                            <td class="px-4 py-3 text-gray-900">{{ $row->plan?->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $row->started_at->format('M j, Y g:i A') }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $row->ended_at ? $row->ended_at->format('M j, Y g:i A') : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-gray-500">No history yet. After an admin approves a request, your periods appear here.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
