<div class="relative mx-auto h-full max-w-3xl">
    <!-- Share Button Trigger (fixed at top-right of viewport) -->
    @can('update', $chat)
        @if ($chat->visibility === \App\Enums\Visibility::Private->value)
            <div class="fixed top-4 right-4 z-50">
                <flux:modal.trigger name="confirm-share">
                    <flux:button size="sm" variant="subtle" icon="link">
                        {{ __('Share') }}
                    </flux:button>
                </flux:modal.trigger>
            </div>
        @else
            <div class="fixed top-4 right-4 z-50">
                <flux:modal.trigger name="confirm-unshare">
                    <flux:button size="sm" variant="subtle" icon="link-slash">
                        {{ __('Unshare') }}
                    </flux:button>
                </flux:modal.trigger>
            </div>
        @endif
        <!-- Confirmation Modal -->
        <flux:modal name="confirm-share" focusable class="max-w-lg">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Share this chat publicly?') }}</flux:heading>
                    <flux:subheading>
                        {{ __('Anyone with the link will be able to view the entire conversation.') }}
                    </flux:subheading>
                </div>

                <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                    <flux:modal.close>
                        <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>

                    <flux:button variant="primary" wire:click="share">{{ __('Share') }}</flux:button>
                </div>
            </div>
        </flux:modal>

        <!-- Unshare Confirmation Modal -->
        <flux:modal name="confirm-unshare" focusable class="max-w-lg">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Stop sharing this chat?') }}</flux:heading>
                    <flux:subheading>
                        {{ __('The link will no longer be accessible to anyone except you.') }}
                    </flux:subheading>
                </div>

                <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                    <flux:modal.close>
                        <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>

                    <flux:button variant="danger" wire:click="unshare">{{ __('Unshare') }}</flux:button>
                </div>
            </div>
        </flux:modal>
    @endcan

    @can('update', $chat)
        <!-- Model Selector (top-left) -->
        <div class="fixed top-4 right-24 z-50">
            <flux:dropdown position="bottom" align="end">
                <flux:button size="sm" variant="subtle" icon="cpu-chip">
                    {{ strtoupper($model) }}
                </flux:button>

                <flux:menu class="min-w-40">
                    <flux:menu.radio.group>
                        @foreach (\App\Enums\OpenAiModel::toArray() as $modelName => $modelValue)
                            <flux:menu.item
                                type="button"
                                wire:click="setModel('{{ $modelValue }}')"
                                :active="$model === $modelValue"
                            >
                                {{ $modelValue }}
                            </flux:menu.item>
                        @endforeach
                    </flux:menu.radio.group>
                </flux:menu>
            </flux:dropdown>
        </div>
    @endcan

    <div
        class="mx-auto flex h-[calc(100vh-10rem)] max-h-screen max-w-3xl flex-1 flex-col-reverse gap-4 overflow-y-scroll py-1 lg:h-[calc(100vh-8rem)]"
    >
        @include('livewire.loading-indicator')

        @foreach (array_reverse($messages) as $message)
            @if ($message instanceof \App\Dtos\UserMessage)
                <x-chat.user-message :message="$message" :index="$loop->index" />
            @elseif ($message instanceof \App\Dtos\AssistantMessage)
                <x-chat.assistant-message :message="$message" :is-first="$loop->first" />
            @endif
        @endforeach

        @can('update', $chat)
            @include('livewire.message-input')
        @endcan
    </div>
</div>
