@php
    use Filament\Facades\Filament;
    use Filament\Support\Icons\Heroicon;

    $user = Filament::auth()->user();
@endphp

@if ($user)
    <div class="sidebar-user-footer">
        <div class="sidebar-user-row">
            <div class="sidebar-user-meta">
                <div class="sidebar-user-label">Account</div>
                <div class="sidebar-user-name">
                    {{ filament()->getUserName($user) }}
                </div>
                <div class="sidebar-user-subtitle">Signed in</div>
            </div>

            <form method="POST" action="{{ Filament::getLogoutUrl() }}">
                @csrf

                <button type="submit" class="sidebar-signout-btn" aria-label="Sign out" title="Sign out">
                    <x-filament::icon :icon="Heroicon::ArrowLeftEndOnRectangle" class="h-4 w-4" />
                </button>
            </form>
        </div>
    </div>
@endif
