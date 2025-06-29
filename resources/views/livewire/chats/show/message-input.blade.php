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
                            <!-- Share/Unshare option -->
                            @if ($chat->visibility === \App\Enums\Visibility::Private->value)
                                <flux:modal.trigger name="confirm-share">
                                    <flux:menu.item icon="link">
                                        {{ __('Share') }}
                                    </flux:menu.item>
                                </flux:modal.trigger>
                            @else
                                <flux:modal.trigger name="confirm-unshare">
                                    <flux:menu.item icon="link-slash">
                                        {{ __('Unshare') }}
                                    </flux:menu.item>
                                </flux:modal.trigger>
                            @endif

                            <flux:menu.separator />

                            <!-- Model Selection -->
                            <flux:menu.submenu heading="Model: {{ strtoupper($model) }}">
                                <flux:menu.radio.group>
                                    @foreach (\App\Enums\OpenAiModel::toArray() as $modelName => $modelValue)
                                        <flux:menu.radio
                                            wire:click="setModel('{{ $modelValue }}')"
                                            :checked="$model === $modelValue"
                                        >
                                            {{ $modelValue }}
                                        </flux:menu.radio>
                                    @endforeach
                                </flux:menu.radio.group>
                            </flux:menu.submenu>

                            <flux:menu.separator />

                            <!-- Settings -->
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
