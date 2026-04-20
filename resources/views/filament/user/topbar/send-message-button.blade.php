@php
    use App\Filament\User\Pages\SendMessage;
@endphp

<a
    href="{{ SendMessage::getUrl(panel: 'user') }}"
    wire:navigate
    class="send-message-topbar-btn"
>
    <span class="send-message-topbar-btn-label">Send Message</span>
</a>
