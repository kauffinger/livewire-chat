<x-layouts.app.sidebar :title="__('Settings')">
    <flux:main>
        <div class="mx-auto w-full max-w-3xl">
            <flux:heading size="xl" level="1">{{ __('Settings') }}</flux:heading>

            <div
                class="mt-6 divide-y divide-zinc-200 border-t border-zinc-200 dark:divide-white/10 dark:border-white/10"
            >
                <livewire:settings.profile />

                <livewire:settings.password />

                <x-settings.section
                    :heading="__('Appearance')"
                    :description="__('Choose how the app looks on this device')"
                >
                    <flux:radio.group
                        x-data
                        variant="segmented"
                        x-model="$flux.appearance"
                        aria-label="{{ __('Appearance') }}"
                    >
                        <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                        <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                        <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
                    </flux:radio.group>
                </x-settings.section>

                <livewire:settings.delete-user-form />
            </div>
        </div>
    </flux:main>
</x-layouts.app.sidebar>
