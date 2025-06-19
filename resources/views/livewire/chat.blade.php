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
        @include('livewire.loading-indicator')

        @foreach (array_reverse($messages) as $message)
            @if ($message instanceof \App\Dtos\UserMessage)
                <x-chat.user-message :message="$message" :index="$loop->index" />
            @elseif ($message instanceof \App\Dtos\AssistantMessage)
                <x-chat.assistant-message :message="$message" :is-first="$loop->first" />
            @elseif ($message instanceof \App\Dtos\ToolResultMessage)
                <x-chat.tool-result-message :message="$message" />
            @endif
        @endforeach

        @can('update', $chat)
            @include('livewire.message-input')
        @endcan
    </div>
</div>
