<x-filament-panels::page>
    <div class="send-campaign-wizard send-campaign-wizard--full" wire:key="business-onboarding-wizard-root">
        <header class="send-campaign-wizard-hero">
            <div class="send-campaign-wizard-hero-inner">
                <p class="send-campaign-wizard-kicker">Welcome</p>
                <h1 class="send-campaign-wizard-title">Set up your business</h1>
                <p class="send-campaign-wizard-lead">
                    Tell us about your business, optionally add a few contacts, and start your free trial if it is available. This only takes a minute.
                </p>
            </div>
        </header>

        <nav class="send-campaign-wizard-progress" aria-label="Onboarding steps">
            @foreach ([1 => 'Business', 2 => 'Category', 3 => 'Contacts', 4 => 'Review'] as $step => $label)
                <button
                    type="button"
                    class="send-campaign-wizard-step {{ $this->wizardStep === $step ? 'send-campaign-wizard-step--active' : '' }} {{ $this->wizardStep > $step ? 'send-campaign-wizard-step--done' : '' }}"
                    wire:click="goToStep({{ $step }})"
                    @disabled($step > $this->wizardStep || $this->isFinishing)
                >
                    <span class="send-campaign-wizard-step-num">{{ $step }}</span>
                    <span class="send-campaign-wizard-step-label">{{ $label }}</span>
                </button>
            @endforeach
        </nav>

        <div class="send-campaign-wizard-panel @if ($this->isFinishing) opacity-60 pointer-events-none @endif">
            @if ($this->wizardStep === 1)
                <div class="send-campaign-wizard-section">
                    <h2 class="send-campaign-wizard-section-title">Business details</h2>
                    <p class="send-campaign-wizard-section-desc">This information helps personalize your workspace and messaging context.</p>

                    <div>
                        <label class="send-campaign-wizard-field-label" for="onboarding-business-name">Business name</label>
                        <input
                            id="onboarding-business-name"
                            type="text"
                            wire:model="business_name"
                            class="send-campaign-wizard-input"
                            autocomplete="organization"
                        />
                        @error('business_name')
                            <p class="send-campaign-wizard-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="send-campaign-wizard-field-label" for="onboarding-business-description">Business description</label>
                        <textarea
                            id="onboarding-business-description"
                            wire:model.live="business_description"
                            rows="5"
                            maxlength="1000"
                            class="send-campaign-wizard-textarea"
                            placeholder="What does your business do?"
                        ></textarea>
                        <div class="send-campaign-wizard-meta-row">
                            <span class="send-campaign-wizard-char-count">{{ strlen($this->business_description) }} / 1000</span>
                        </div>
                        @error('business_description')
                            <p class="send-campaign-wizard-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @endif

            @if ($this->wizardStep === 2)
                <div class="send-campaign-wizard-section">
                    <h2 class="send-campaign-wizard-section-title">Business category</h2>
                    <p class="send-campaign-wizard-section-desc">Pick the category that best describes you. You can change this later in settings.</p>

                    <div>
                        <label class="send-campaign-wizard-field-label" for="onboarding-category">Category</label>
                        <select id="onboarding-category" wire:model="business_category" class="send-campaign-wizard-input">
                            <option value="">Select a category…</option>
                            @foreach ($this->getBusinessCategoryOptions() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('business_category')
                            <p class="send-campaign-wizard-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @endif

            @if ($this->wizardStep === 3)
                <div class="send-campaign-wizard-section">
                    <h2 class="send-campaign-wizard-section-title">Contacts</h2>
                    <p class="send-campaign-wizard-section-desc">
                        Optionally add people you message often. Skip this step if you prefer to add contacts later from the dashboard.
                    </p>

                    <div class="send-campaign-wizard-field-block space-y-4">
                        @foreach ($this->contactRows as $index => $_row)
                            <div class="send-campaign-wizard-contact-row grid gap-3 sm:grid-cols-[1fr_1fr_1fr_auto] sm:items-end" wire:key="contact-row-{{ $index }}">
                                <div>
                                    <label class="send-campaign-wizard-field-label" for="contact-name-{{ $index }}">Name</label>
                                    <input
                                        id="contact-name-{{ $index }}"
                                        type="text"
                                        wire:model="contactRows.{{ $index }}.name"
                                        class="send-campaign-wizard-input"
                                    />
                                </div>
                                <div>
                                    <label class="send-campaign-wizard-field-label" for="contact-phone-{{ $index }}">Phone</label>
                                    <input
                                        id="contact-phone-{{ $index }}"
                                        type="tel"
                                        wire:model="contactRows.{{ $index }}.phone_number"
                                        class="send-campaign-wizard-input"
                                    />
                                </div>
                                <div>
                                    <label class="send-campaign-wizard-field-label" for="contact-email-{{ $index }}">Email (optional)</label>
                                    <input
                                        id="contact-email-{{ $index }}"
                                        type="email"
                                        wire:model="contactRows.{{ $index }}.email"
                                        class="send-campaign-wizard-input"
                                    />
                                </div>
                                <div class="flex justify-end sm:pb-0.5">
                                    @if (count($this->contactRows) > 1)
                                        <button
                                            type="button"
                                            wire:click="removeContactRow({{ $index }})"
                                            class="send-campaign-wizard-btn send-campaign-wizard-btn--ghost text-sm"
                                        >
                                            Remove
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <button
                            type="button"
                            wire:click="addContactRow"
                            class="send-campaign-wizard-btn send-campaign-wizard-btn--ghost w-full sm:w-auto"
                            @disabled(count($this->contactRows) >= 25)
                        >
                            Add another contact
                        </button>

                        @if ($errors->has('contactRows'))
                            <p class="send-campaign-wizard-error">{{ $errors->first('contactRows') }}</p>
                        @endif
                    </div>
                </div>
            @endif

            @if ($this->wizardStep === 4)
                <div class="send-campaign-wizard-section">
                    <h2 class="send-campaign-wizard-section-title">Review & finish</h2>
                    <p class="send-campaign-wizard-section-desc">Confirm your details, then complete setup. Next you will open Subscription to manage your plan.</p>

                    <dl class="send-campaign-wizard-summary">
                        <div class="send-campaign-wizard-summary-row">
                            <dt>Business</dt>
                            <dd>{{ $this->business_name ?: '—' }}</dd>
                        </div>
                        <div class="send-campaign-wizard-summary-row send-campaign-wizard-summary-row--block">
                            <dt>Description</dt>
                            <dd class="send-campaign-wizard-summary-message">{{ $this->business_description ?: '—' }}</dd>
                        </div>
                        <div class="send-campaign-wizard-summary-row">
                            <dt>Category</dt>
                            <dd>{{ $this->getBusinessCategoryLabel() }}</dd>
                        </div>
                        <div class="send-campaign-wizard-summary-row">
                            <dt>Contacts to add</dt>
                            <dd>{{ $this->getFilledContactRowCount() }}</dd>
                        </div>
                    </dl>

                    @if ($this->canOfferFreeTrial())
                        <div class="send-campaign-wizard-callout mt-6 border-violet-200/80 bg-violet-50/80 text-violet-950">
                            <p class="send-campaign-wizard-callout-title text-violet-900">Free trial</p>
                            <p class="send-campaign-wizard-callout-text text-violet-900/90">
                                Start a {{ \App\Services\SubscriptionService::FREE_TRIAL_DAYS }}-day trial on the free plan with daily SMS limits. You can upgrade anytime.
                            </p>
                            <label class="send-campaign-wizard-schedule-toggle mt-4 border-violet-200 bg-white/80">
                                <input type="checkbox" wire:model.live="startFreeTrial" class="send-campaign-wizard-schedule-checkbox" />
                                <span class="send-campaign-wizard-schedule-label">Start free trial now</span>
                            </label>
                        </div>
                    @endif
                </div>
            @endif

            <div class="send-campaign-wizard-actions">
                @if ($this->wizardStep > 1)
                    <button
                        type="button"
                        wire:click="previousStep"
                        class="send-campaign-wizard-btn send-campaign-wizard-btn--ghost"
                        wire:loading.attr="disabled"
                        @disabled($this->isFinishing)
                    >
                        Back
                    </button>
                @else
                    <span></span>
                @endif

                @if ($this->wizardStep < 4)
                    <button
                        type="button"
                        wire:click="nextStep"
                        class="send-campaign-wizard-btn send-campaign-wizard-btn--primary"
                        wire:loading.attr="disabled"
                        @disabled($this->isFinishing)
                    >
                        <span wire:loading.remove wire:target="nextStep">Continue</span>
                        <span wire:loading wire:target="nextStep">Checking…</span>
                    </button>
                @else
                    <button
                        type="button"
                        wire:click="finishOnboarding"
                        class="send-campaign-wizard-btn send-campaign-wizard-btn--primary"
                        wire:loading.attr="disabled"
                        @disabled($this->isFinishing)
                    >
                        <span wire:loading.remove wire:target="finishOnboarding">Complete setup</span>
                        <span wire:loading wire:target="finishOnboarding">Saving…</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if ($isFinishing)
        <div
            class="send-campaign-wizard-finishing"
            x-data
            x-init="setTimeout(() => $wire.redirectToSubscriptionPage(), 1500)"
        >
            <div class="send-campaign-wizard-finishing-spinner" aria-hidden="true"></div>
            <p class="send-campaign-wizard-finishing-title">Setting up your workspace</p>
            <p class="send-campaign-wizard-finishing-text">You will be redirected to Subscription in a moment.</p>
        </div>
    @endif
</x-filament-panels::page>
