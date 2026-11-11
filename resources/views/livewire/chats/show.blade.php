<div class="mx-auto flex h-[calc(100dvh-6.5rem)] max-w-3xl flex-col gap-4 lg:h-[calc(100dvh-4rem)]">
    <div
        class="flex min-h-0 flex-1 flex-col-reverse overflow-y-auto"
        x-data
        x-init="$el.scrollTop = $el.scrollHeight"
    >
        <div class="mb-auto flex flex-col gap-6 py-1">
            @if ($conversationId === null && $pendingMessage === '')
                <flux:heading size="xl" level="1" class="py-12 text-center">
                    {{ __('What can I help you with?') }}
                </flux:heading>
            @endif

            @island(name: 'messages')
                @if ($this->history?->hasMorePages())
                    <div
                        wire:key="earlier-messages-{{ $messagesPerPage }}"
                        wire:intersect="showEarlierMessages"
                        class="flex justify-center"
                    >
                        <flux:icon.loading class="size-4 text-zinc-400" />
                    </div>
                @endif

                @foreach ($this->messages as $message)
                    <div wire:key="message-{{ $message['id'] }}">
                        @if ($message['role'] === 'user')
                            <x-chat.user-message :message="$message" />
                        @elseif ($message['role'] === 'assistant')
                            <x-chat.assistant-message :message="$message" />
                        @endif
                    </div>
                @endforeach
            @endisland

            @if ($pendingMessage !== '')
                <div wire:key="pending-user-message">
                    <x-chat.user-message :message="['content' => $pendingMessage]" />
                </div>
            @endif

            @if ($this->pendingApprovals !== [])
                <div wire:key="pending-approvals" wire:loading.remove wire:target="approveToolCalls, rejectToolCalls">
                    <x-chat.tool-approvals :approvals="$this->pendingApprovals" />
                </div>
            @endif

            <div
                wire:loading.block
                wire:target="runAgent, approveToolCalls, rejectToolCalls"
                wire:key="streaming-message-{{ $streamedTurns }}"
                class="hidden"
            >
                <x-chat.assistant-message :is-loading="true" wire-stream="streamed-message" :wire-replace="true" />
            </div>
        </div>
    </div>

    @if ($this->pendingApprovals === [])
        @include('livewire.chats.show.message-input')
    @endif
</div>
