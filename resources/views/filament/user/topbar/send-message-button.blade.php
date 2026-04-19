@php
    use App\Filament\User\Pages\SendCampaign;
@endphp

<a
    href="{{ SendCampaign::getUrl(panel: 'user') }}"
    wire:navigate
    class="send-message-topbar-btn"
>
    <span class="send-message-topbar-btn-label">Send Message</span>
</a>
