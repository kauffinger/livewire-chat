<div class="relative mx-auto h-full max-w-3xl">
    <div
        class="mx-auto flex h-[calc(100vh-10rem)] max-h-screen max-w-3xl flex-1 flex-col-reverse overflow-y-scroll lg:h-[calc(100vh-8rem)]"
        x-data
        x-init="$el.scrollTop = $el.scrollHeight"
    >
        <div class="mb-auto flex flex-col gap-4 py-1">
            @if ($this->history?->hasMorePages())
                <div wire:key="earlier-messages" class="flex justify-center">
                    <flux:button size="sm" variant="ghost" icon="arrow-up" wire:click="showEarlierMessages">
                        {{ __('Show earlier messages') }}
                    </flux:button>
                </div>
            @endif

            @if ($this->messages === [] && $pendingMessage === '')
                <flux:heading size="xl" level="1" class="py-12 text-center">
                    {{ __('What can I help you with?') }}
                </flux:heading>
            @endif

            @foreach ($this->messages as $message)
                <div wire:key="message-{{ $message['id'] }}">
                    @if ($message['role'] === 'user')
                        <x-chat.user-message :message="$message" :index="$message['id']" />
                    @elseif ($message['role'] === 'assistant')
                        <x-chat.assistant-message :message="$message" />
                    @endif
                </div>
            @endforeach

            @if ($pendingMessage !== '')
                <div wire:key="pending-user-message">
                    <x-chat.user-message :message="['content' => $pendingMessage]" index="pending" />
                </div>
            @endif

            @if ($this->pendingApprovals !== [])
                <div wire:key="pending-approvals" wire:loading.remove wire:target="approveToolCalls, rejectToolCalls">
                    <x-chat.tool-approvals :approvals="$this->pendingApprovals" />
                </div>
            @endif

            <div
                wire:loading.flex
                wire:target="runAgent, approveToolCalls, rejectToolCalls"
                wire:key="streaming-message-{{ $streamedTurns }}"
                class="relative mx-auto hidden w-full max-w-4xl"
            >
                <x-chat.assistant-message :is-loading="true" wire-stream="streamed-message" :wire-replace="true" />
            </div>
        </div>

        @if ($this->pendingApprovals === [])
            @include('livewire.chats.show.message-input')
        @endif
    </div>
</div>
