@php
    use Filament\Support\Icons\Heroicon;
@endphp

<x-filament::modal width="2xl">
    <x-slot name="trigger">
        <x-filament::icon-button
            color="gray"
            size="md"
            :icon="Heroicon::OutlinedQuestionMarkCircle"
            label="Open tutorial"
            class="ms-2"
        />
    </x-slot>

    <x-slot name="heading">
        Quick Start Tutorial
    </x-slot>

    <x-slot name="description">
        Learn the basic flow to use your messaging dashboard in under a minute.
    </x-slot>

    <div x-data="{ step: 1 }" class="space-y-5">
        <div class="flex items-center gap-2 text-xs text-gray-500">
            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full border" :class="step >= 1 ? 'border-primary-500 text-primary-600' : 'border-gray-300'">1</span>
            <span class="h-px w-8 bg-gray-300"></span>
            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full border" :class="step >= 2 ? 'border-primary-500 text-primary-600' : 'border-gray-300'">2</span>
            <span class="h-px w-8 bg-gray-300"></span>
            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full border" :class="step >= 3 ? 'border-primary-500 text-primary-600' : 'border-gray-300'">3</span>
            <span class="h-px w-8 bg-gray-300"></span>
            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full border" :class="step >= 4 ? 'border-primary-500 text-primary-600' : 'border-gray-300'">4</span>
        </div>

        <div x-show="step === 1" x-transition>
            <h4 class="text-base font-semibold text-gray-900">Step 1: Add contacts</h4>
            <p class="mt-2 text-sm text-gray-600">Go to <strong>Contacts</strong>, then click <strong>Create contact</strong> to save name and phone number.</p>
        </div>

        <div x-show="step === 2" x-transition>
            <h4 class="text-base font-semibold text-gray-900">Step 2: Create your message</h4>
            <p class="mt-2 text-sm text-gray-600">Open <strong>Messages</strong> and create a new SMS template. Keep your content clear and concise.</p>
        </div>

        <div x-show="step === 3" x-transition>
            <h4 class="text-base font-semibold text-gray-900">Step 3: Send campaign</h4>
            <p class="mt-2 text-sm text-gray-600">Open <strong>Send Campaign</strong>, choose recipients, then dispatch your message.</p>
        </div>

        <div x-show="step === 4" x-transition>
            <h4 class="text-base font-semibold text-gray-900">Step 4: Track results</h4>
            <p class="mt-2 text-sm text-gray-600">Return to the dashboard to monitor deliveries, failures, and campaign trends using the charts.</p>
        </div>

        <div class="flex items-center justify-between border-t pt-4">
            <x-filament::button color="gray" x-on:click="step = Math.max(1, step - 1)">
                Previous
            </x-filament::button>

            <div class="text-xs text-gray-500" x-text="`Step ${step} of 4`"></div>

            <x-filament::button x-on:click="step = Math.min(4, step + 1)">
                Next
            </x-filament::button>
        </div>
    </div>
</x-filament::modal>

