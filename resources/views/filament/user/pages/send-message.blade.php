<x-filament-panels::page>
    <div class="send-campaign-wizard" wire:key="send-message-root">
        <header class="send-campaign-wizard-hero">
            <div class="send-campaign-wizard-hero-inner">
                <p class="send-campaign-wizard-kicker">Messaging</p>
                <h1 class="send-campaign-wizard-title">Send Message</h1>
                <p class="send-campaign-wizard-lead">
                    Send one SMS to one contact quickly. For bulk sends, use Send Campaign.
                </p>
            </div>
        </header>

        <div class="send-campaign-wizard-panel">
            <div class="send-campaign-wizard-section">
                <h2 class="send-campaign-wizard-section-title">Compose message</h2>
                <p class="send-campaign-wizard-section-desc">Choose a contact and write your message (160 characters max).</p>

                <div>
                    <label class="send-campaign-wizard-field-label" for="send-message-contact">Contact</label>
                    <input
                        id="send-message-contact-search"
                        type="text"
                        wire:model.live.debounce.250ms="contactSearch"
                        class="send-campaign-wizard-input"
                        placeholder="Search by name, phone, or email..."
                    />
                    <select id="send-message-contact" wire:model="contactId" class="send-campaign-wizard-input">
                        <option value="">Select contact...</option>
                        @foreach ($this->getFilteredContactsForSendMessage() as $contact)
                            <option value="{{ $contact->id }}">{{ $contact->name }} ({{ $contact->phone_number }})</option>
                        @endforeach
                    </select>
                    @if ($this->getFilteredContactsForSendMessage()->isEmpty())
                        <p class="send-campaign-wizard-empty mt-2">No contacts match your search.</p>
                    @endif
                    @error('contactId')
                        <p class="send-campaign-wizard-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="send-campaign-wizard-callout">
                    <p class="send-campaign-wizard-callout-title">Content policy</p>
                    <p class="send-campaign-wizard-callout-text">Avoid URLs/links and profanity. Violations may still show as sent, but messages may not be delivered and penalties may apply.</p>
                    <a href="{{ \App\Filament\User\Pages\MessagePolicy::getUrl(panel: 'user') }}" class="send-campaign-wizard-callout-link">Read full policy</a>
                </div>

                <div>
                    <label class="send-campaign-wizard-field-label" for="send-message-content">Message</label>
                    <textarea
                        id="send-message-content"
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

                <dl class="send-campaign-wizard-summary">
                    <div class="send-campaign-wizard-summary-row">
                        <dt>Recipient</dt>
                        <dd>{{ $this->getSelectedContactLabel() }}</dd>
                    </div>
                    <div class="send-campaign-wizard-summary-row">
                        <dt>Message length</dt>
                        <dd>{{ strlen($this->content) }} character(s)</dd>
                    </div>
                </dl>
            </div>

            <div class="send-campaign-wizard-actions">
                <a
                    href="{{ \App\Filament\User\Pages\SendCampaign::getUrl(panel: 'user') }}"
                    wire:navigate
                    class="send-campaign-wizard-btn send-campaign-wizard-btn--ghost"
                >
                    Go to Send Campaign
                </a>

                <button type="button" wire:click="send" class="send-campaign-wizard-btn send-campaign-wizard-btn--primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="send">Send message</span>
                    <span wire:loading wire:target="send">Sending…</span>
                </button>
            </div>
        </div>
    </div>
</x-filament-panels::page>
