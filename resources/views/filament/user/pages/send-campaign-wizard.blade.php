<x-filament-panels::page>
    <div class="send-campaign-wizard" wire:key="send-campaign-wizard-root">
        <header class="send-campaign-wizard-hero">
            <div class="send-campaign-wizard-hero-inner">
                <p class="send-campaign-wizard-kicker">Campaign</p>
                <h1 class="send-campaign-wizard-title">Send a campaign</h1>
                <p class="send-campaign-wizard-lead">
                    Walk through audience, message, and timing in a few steps. You can send now or schedule for later.
                </p>
            </div>
        </header>

        <nav class="send-campaign-wizard-progress" aria-label="Campaign steps">
            @foreach ([1 => 'Audience', 2 => 'Message', 3 => 'Timing', 4 => 'Review'] as $step => $label)
                <button
                    type="button"
                    class="send-campaign-wizard-step {{ $this->wizardStep === $step ? 'send-campaign-wizard-step--active' : '' }} {{ $this->wizardStep > $step ? 'send-campaign-wizard-step--done' : '' }}"
                    wire:click="goToStep({{ $step }})"
                    @disabled($step > $this->wizardStep)
                >
                    <span class="send-campaign-wizard-step-num">{{ $step }}</span>
                    <span class="send-campaign-wizard-step-label">{{ $label }}</span>
                </button>
            @endforeach
        </nav>

        <div class="send-campaign-wizard-panel">
            @if ($this->wizardStep === 1)
                <div class="send-campaign-wizard-section">
                    <h2 class="send-campaign-wizard-section-title">Who should receive this?</h2>
                    <p class="send-campaign-wizard-section-desc">Choose your audience. Groups only include contacts you have tagged with those labels.</p>

                    <div class="send-campaign-wizard-choice-grid">
                        <label class="send-campaign-wizard-choice {{ $this->audience === 'all' ? 'send-campaign-wizard-choice--checked' : '' }}">
                            <input type="radio" wire:model.live="audience" value="all" class="send-campaign-wizard-choice-input" />
                            <span class="send-campaign-wizard-choice-title">All contacts</span>
                            <span class="send-campaign-wizard-choice-text">Everyone in your contact list.</span>
                        </label>
                        <label class="send-campaign-wizard-choice {{ $this->audience === 'contacts' ? 'send-campaign-wizard-choice--checked' : '' }}">
                            <input type="radio" wire:model.live="audience" value="contacts" class="send-campaign-wizard-choice-input" />
                            <span class="send-campaign-wizard-choice-title">Pick contacts</span>
                            <span class="send-campaign-wizard-choice-text">Select specific people only.</span>
                        </label>
                        <label class="send-campaign-wizard-choice {{ $this->audience === 'groups' ? 'send-campaign-wizard-choice--checked' : '' }}">
                            <input type="radio" wire:model.live="audience" value="groups" class="send-campaign-wizard-choice-input" />
                            <span class="send-campaign-wizard-choice-title">Send to group</span>
                            <span class="send-campaign-wizard-choice-text">Everyone in one or more of your groups.</span>
                        </label>
                    </div>

                    @if ($this->audience === 'contacts')
                        <div class="send-campaign-wizard-field-block">
                            <span class="send-campaign-wizard-field-label">Select contacts</span>
                            <div class="send-campaign-wizard-checkbox-grid">
                                @forelse ($this->getContactsForWizard() as $contact)
                                    <label class="send-campaign-wizard-check" wire:key="contact-{{ $contact->id }}">
                                        <input type="checkbox" wire:model="contactIds" value="{{ $contact->id }}" class="send-campaign-wizard-check-input" />
                                        <span class="send-campaign-wizard-check-text">{{ $contact->name }}</span>
                                        <span class="send-campaign-wizard-check-sub">{{ $contact->phone_number }}</span>
                                    </label>
                                @empty
                                    <p class="send-campaign-wizard-empty">No contacts yet. Add contacts first.</p>
                                @endforelse
                            </div>
                            @error('contactIds')
                                <p class="send-campaign-wizard-error">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    @if ($this->audience === 'groups')
                        <div class="send-campaign-wizard-field-block">
                            <span class="send-campaign-wizard-field-label">Select groups</span>
                            <div class="send-campaign-wizard-checkbox-grid">
                                @forelse ($this->getGroupsForWizard() as $group)
                                    <label class="send-campaign-wizard-check" wire:key="group-{{ $group->id }}">
                                        <input type="checkbox" wire:model="groupIds" value="{{ $group->id }}" class="send-campaign-wizard-check-input" />
                                        <span class="send-campaign-wizard-check-text">{{ $group->name }}</span>
                                    </label>
                                @empty
                                    <p class="send-campaign-wizard-empty">You have no groups yet. Create groups under Messaging → Groups.</p>
                                @endforelse
                            </div>
                            @error('groupIds')
                                <p class="send-campaign-wizard-error">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>
            @endif

            @if ($this->wizardStep === 2)
                <div class="send-campaign-wizard-section">
                    <h2 class="send-campaign-wizard-section-title">Your message</h2>
                    <p class="send-campaign-wizard-section-desc">Keep it clear and within 160 characters for a single SMS segment.</p>

                    <div class="send-campaign-wizard-callout">
                        <p class="send-campaign-wizard-callout-title">Content policy</p>
                        <p class="send-campaign-wizard-callout-text">Avoid URLs/links and profanity. Violations may still show as sent, but messages may not be delivered and penalties may apply.</p>
                        <a href="{{ \App\Filament\User\Pages\MessagePolicy::getUrl(panel: 'user') }}" class="send-campaign-wizard-callout-link">Read full policy</a>
                    </div>

                    <label class="send-campaign-wizard-field-label" for="campaign-content">Message</label>
                    <textarea
                        id="campaign-content"
                        wire:model.live="content"
                        rows="5"
                        maxlength="160"
                        class="send-campaign-wizard-textarea"
                        placeholder="Type your SMS (160 characters max)…"
                    ></textarea>
                    <div class="send-campaign-wizard-meta-row">
                        <span class="send-campaign-wizard-char-count">{{ strlen($this->content) }} / 160</span>
                    </div>
                    @error('content')
                        <p class="send-campaign-wizard-error">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            @if ($this->wizardStep === 3)
                <div class="send-campaign-wizard-section">
                    <h2 class="send-campaign-wizard-section-title">When should it go out?</h2>
                    <p class="send-campaign-wizard-section-desc">Send immediately or pick a future date and time ({{ $this->getCampaignTimezoneLabel() }}).</p>

                    <div class="send-campaign-wizard-toggle-row">
                        <label class="send-campaign-wizard-schedule-toggle">
                            <input type="checkbox" wire:model.live="scheduleCampaign" class="send-campaign-wizard-schedule-checkbox" />
                            <span class="send-campaign-wizard-schedule-label">Schedule for later</span>
                        </label>
                    </div>

                    @if ($this->scheduleCampaign)
                        <div class="send-campaign-wizard-datetime-row">
                            <div>
                                <label class="send-campaign-wizard-field-label" for="scheduled-date">Date</label>
                                <input id="scheduled-date" type="date" wire:model="scheduledDate" class="send-campaign-wizard-input" />
                                @error('scheduledDate')
                                    <p class="send-campaign-wizard-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="send-campaign-wizard-field-label" for="scheduled-time">Time</label>
                                <input id="scheduled-time" type="time" wire:model="scheduledTime" class="send-campaign-wizard-input" />
                                @error('scheduledTime')
                                    <p class="send-campaign-wizard-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            @if ($this->wizardStep === 4)
                <div class="send-campaign-wizard-section">
                    <h2 class="send-campaign-wizard-section-title">Review & send</h2>
                    <p class="send-campaign-wizard-section-desc">Confirm the details below, then send or queue your campaign.</p>

                    <dl class="send-campaign-wizard-summary">
                        <div class="send-campaign-wizard-summary-row">
                            <dt>Audience</dt>
                            <dd>{{ $this->getAudienceSummary() }}</dd>
                        </div>
                        <div class="send-campaign-wizard-summary-row">
                            <dt>Recipients</dt>
                            <dd>{{ $this->getRecipientCount() }} contact(s)</dd>
                        </div>
                        <div class="send-campaign-wizard-summary-row">
                            <dt>Timing</dt>
                            <dd>{{ $this->getTimingSummary() }}</dd>
                        </div>
                        <div class="send-campaign-wizard-summary-row send-campaign-wizard-summary-row--block">
                            <dt>Message</dt>
                            <dd class="send-campaign-wizard-summary-message">{{ $this->content ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>
            @endif

            <div class="send-campaign-wizard-actions">
                @if ($this->wizardStep > 1)
                    <button type="button" wire:click="previousStep" class="send-campaign-wizard-btn send-campaign-wizard-btn--ghost" wire:loading.attr="disabled">
                        Back
                    </button>
                @else
                    <span></span>
                @endif

                @if ($this->wizardStep < 4)
                    <button type="button" wire:click="nextStep" class="send-campaign-wizard-btn send-campaign-wizard-btn--primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="nextStep">Continue</span>
                        <span wire:loading wire:target="nextStep">Checking…</span>
                    </button>
                @else
                    <button type="button" wire:click="send" class="send-campaign-wizard-btn send-campaign-wizard-btn--primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="send">{{ $this->scheduleCampaign ? 'Schedule campaign' : 'Send campaign' }}</span>
                        <span wire:loading wire:target="send">Working…</span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
