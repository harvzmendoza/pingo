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

                <x-filament::button
                    class="mt-5 w-full"
                    color="primary"
                    wire:click="subscribe({{ $plan->id }})"
                >
                    Subscribe
                </x-filament::button>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
