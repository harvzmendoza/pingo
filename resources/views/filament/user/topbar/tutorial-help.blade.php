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

    <x-slot name="heading">Messaging Quick Start</x-slot>

    <x-slot name="description">Follow these steps to send safely and avoid policy penalties.</x-slot>

    <div x-data="{ step: 1, total: 5 }" class="space-y-5">
        <div class="tutorial-progress">
            <template x-for="index in total" :key="index">
                <span
                    class="tutorial-progress-dot"
                    :class="step >= index ? 'is-active' : ''"
                    x-text="index"
                ></span>
            </template>
        </div>

        <div class="tutorial-card" x-show="step === 1" x-transition>
            <p class="tutorial-step-label">Step 1</p>
            <h4 class="text-base font-semibold text-gray-900">Add contacts</h4>
            <p class="mt-2 text-sm text-gray-600">Go to <strong>Contacts</strong> and click <strong>Create contact</strong> to save a valid recipient number.</p>
        </div>

        <div class="tutorial-card" x-show="step === 2" x-transition>
            <p class="tutorial-step-label">Step 2</p>
            <h4 class="text-base font-semibold text-gray-900">Create your message</h4>
            <p class="mt-2 text-sm text-gray-600">Open <strong>Messages</strong> and create a clear SMS template. Keep content short and direct.</p>
        </div>

        <div class="tutorial-card" x-show="step === 3" x-transition>
            <p class="tutorial-step-label">Step 3</p>
            <h4 class="text-base font-semibold text-gray-900">Review message policy</h4>
            <p class="mt-2 mb-3 text-sm text-gray-600">Before sending, review prohibited content like links and profanity to avoid credit deductions.</p>
            <div class="mt-3">
                <x-filament::button
                    tag="a"
                    color="gray"
                    :href="url('/user/message-policy')"
                >
                    Open Message Policy
                </x-filament::button>
            </div>
        </div>

        <div class="tutorial-card" x-show="step === 4" x-transition>
            <p class="tutorial-step-label">Step 4</p>
            <h4 class="text-base font-semibold text-gray-900">Send campaign</h4>
            <p class="mt-2 text-sm text-gray-600">Open <strong>Send Campaign</strong>, choose recipients, and dispatch your approved message.</p>
        </div>

        <div class="tutorial-card" x-show="step === 5" x-transition>
            <p class="tutorial-step-label">Step 5</p>
            <h4 class="text-base font-semibold text-gray-900">Track results</h4>
            <p class="mt-2 text-sm text-gray-600">Return to the dashboard to monitor deliveries, failures, and campaign trends.</p>
        </div>

        <div class="flex items-center justify-between border-t border-gray-200 pt-4">
            <x-filament::button color="gray" x-on:click="step = Math.max(1, step - 1)">
                Previous
            </x-filament::button>

            <div class="text-xs text-gray-500" x-text="`Step ${step} of ${total}`"></div>

            <x-filament::button x-on:click="step = Math.min(total, step + 1)">
                Next
            </x-filament::button>
        </div>
    </div>
</x-filament::modal>

