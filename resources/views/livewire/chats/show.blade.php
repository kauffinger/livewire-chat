<div class="relative mx-auto h-full max-w-3xl">
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

    <div
        class="mx-auto flex h-[calc(100vh-10rem)] max-h-screen max-w-3xl flex-1 flex-col-reverse gap-4 overflow-y-scroll py-1 lg:h-[calc(100vh-8rem)]"
        x-data
        x-init="$el.scrollTop = $el.scrollHeight"
    >
        <div
            wire:loading.flex
            wire:target="runChatToolLoop"
            wire:key="loading-{{ count($this->messages) }}"
            class="relative mx-auto hidden w-full max-w-4xl flex-1"
        >
            <x-chat.assistant-message :is-loading="true" wire-stream="streamed-message" :wire-replace="true" />
        </div>

        @foreach (array_reverse($this->messages) as $message)
            @if ($message->role === 'user')
                <x-chat.user-message :message="$message" :index="$loop->index" />
            @elseif ($message->role === 'assistant')
                <x-chat.assistant-message :message="$message" :is-first="$loop->first" />
            @endif
        @endforeach

        @auth
            @can('update', $chat)
                @include('livewire.chats.show.message-input')
            @endcan
        @endauth
    </div>
</div>
