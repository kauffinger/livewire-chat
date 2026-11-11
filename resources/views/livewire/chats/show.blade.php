@php
    $isEmpty = $conversationId === null && $pendingMessage === '';
@endphp

<div class="flex h-[calc(100dvh-6.5rem)] flex-col gap-4 lg:h-[calc(100dvh-4rem)]">
    <div
        class="-me-6 flex min-h-0 flex-1 [scrollbar-gutter:stable_both-edges] flex-col-reverse overflow-y-auto mask-t-from-[calc(100%-1.5rem)] pe-6 lg:-me-8 lg:pe-8"
        x-data
        x-init="$el.scrollTop = $el.scrollHeight"
    >
        <div
            @class([
                'mx-auto flex w-full max-w-3xl flex-col gap-6 pt-6 pb-1',
                'my-auto' => $isEmpty,
                'mb-auto' => ! $isEmpty,
            ])
        >
            @if ($isEmpty)
                <div class="flex flex-col items-center gap-6 text-center">
                    <flux:heading size="xl" level="1">
                        {{ __('What can I help you with?') }}
                    </flux:heading>

                    <div class="flex flex-wrap justify-center gap-2">
                        @foreach ([__('Add 12.5 and 30 with the sum tool'), __('Change my profile name to Ada Lovelace'), __('Explain Livewire islands in three sentences')] as $suggestion)
                            <flux:button
                                size="sm"
                                x-on:click="
                                    $wire.newMessage = {{ Js::from($suggestion) }};
                                    $wire.$el.querySelector('textarea').focus()
                                "
                            >
                                {{ $suggestion }}
                            </flux:button>
                        @endforeach
                    </div>
                </div>
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
                <div wire:key="pending-user-message" class="motion-safe:animate-message-in">
                    <x-chat.user-message :message="['content' => $pendingMessage]" />
                </div>
            @endif

            @if ($this->pendingApprovals !== [])
                <div
                    wire:key="pending-approvals"
                    wire:loading.remove
                    wire:target="approveToolCalls, rejectToolCalls"
                    class="motion-safe:animate-message-in"
                >
                    <x-chat.tool-approvals :approvals="$this->pendingApprovals" />
                </div>
            @endif

            <div
                wire:loading.block
                wire:target="runAgent, approveToolCalls, rejectToolCalls"
                wire:key="streaming-message-{{ $streamedTurns }}"
                class="motion-safe:animate-message-in hidden"
            >
                <x-chat.assistant-message :is-loading="true" wire-stream="streamed-message" :wire-replace="true" />
            </div>
        </div>
    </div>

    @if ($this->pendingApprovals === [])
        @include('livewire.chats.show.message-input')
    @endif
</div>
