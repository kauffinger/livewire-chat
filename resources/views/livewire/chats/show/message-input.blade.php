<div class="absolute right-0 bottom-0 left-0 flex flex-row items-center justify-between">
    <div class="flex w-full flex-row gap-2">
        <flux:input
            wire:keydown.enter="sendMessage"
            wire:model="newMessage"
            type="text"
            class=""
            placeholder="Type your message here..."
        >
            <x-slot name="iconTrailing">
                <div class="flex items-center gap-1">
                    <flux:button
                        size="sm"
                        variant="subtle"
                        icon="paper-airplane"
                        class="-mr-1"
                        wire:click="sendMessage"
                    />
                    <flux:dropdown position="top" align="end">
                        <flux:button size="sm" variant="subtle" icon="cog" class="-mr-1" />

                        <flux:menu class="min-w-40">
                            <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
                                {{ __('Settings') }}
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </x-slot>
        </flux:input>
    </div>
</div>
