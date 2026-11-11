<section class="mx-auto w-full max-w-3xl">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Appearance')" :subheading="__('Choose how the app looks on this device')">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance" aria-label="{{ __('Appearance') }}">
            <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
        </flux:radio.group>
    </x-settings.layout>
</section>
