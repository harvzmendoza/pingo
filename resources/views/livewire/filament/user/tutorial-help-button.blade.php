@php
    use Filament\Support\Icons\Heroicon;
@endphp

<div>
    <x-filament::icon-button
        color="primary"
        size="md"
        :icon="Heroicon::OutlinedQuestionMarkCircle"
        label="Open tutorial"
        class="ms-2"
        wire:click="mountAction('openTutorial')"
    />

    <x-filament-actions::modals />
</div>

