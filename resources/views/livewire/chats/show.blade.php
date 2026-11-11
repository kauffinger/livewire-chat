@php
    $isEmpty = $conversationId === null && $pendingMessage === '';
@endphp

<div
    @class([
        'flex h-[calc(100dvh-6.5rem)] flex-col lg:h-[calc(100dvh-4rem)]',
        'justify-center gap-8 pb-[12dvh]' => $isEmpty,
        'gap-4' => ! $isEmpty,
    ])
>
    @if ($isEmpty)
        <flux:heading
            wire:key="greeting"
            level="1"
            class="text-center text-3xl! font-medium! tracking-tight text-balance sm:text-4xl!"
        >
            {{ __('What can I help you with?') }}
        </flux:heading>
    @endif

    <div
        wire:key="conversation"
        @class(['relative min-h-0 flex-1', 'hidden' => $isEmpty, 'flex' => ! $isEmpty])
        x-data="{ isScrolledUp: false }"
    >
        <div
            class="-me-6 flex min-h-0 flex-1 [scrollbar-gutter:stable_both-edges] flex-col-reverse overflow-y-auto mask-t-from-[calc(100%-1.5rem)] pe-6 lg:-me-8 lg:pe-8"
            x-ref="messages"
            x-init="$el.scrollTop = $el.scrollHeight"
            x-on:scroll.passive="isScrolledUp = Math.abs($el.scrollTop) > 240"
        >
            <div class="mx-auto mb-auto flex w-full max-w-3xl flex-col gap-8 pt-6 pb-2">
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

                <div
                    wire:loading.block
                    wire:target="runAgent, approveToolCalls, rejectToolCalls"
                    wire:key="streaming-message-{{ $streamedTurns }}"
                    class="motion-safe:animate-message-in hidden"
                >
                    <x-chat.assistant-message
                        :is-loading="true"
                        wire-stream="streamed-message"
                        :wire-replace="true"
                    />
                </div>
            </div>
        </div>

        <div
            class="pointer-events-none absolute inset-x-0 bottom-2 flex justify-center"
            style="display: none"
            x-show="isScrolledUp"
            x-transition.opacity
        >
            <flux:button
                size="sm"
                icon="arrow-down"
                class="pointer-events-auto rounded-full! shadow-md"
                :tooltip="__('Jump to the latest message')"
                aria-label="{{ __('Jump to the latest message') }}"
                x-on:click="$refs.messages.scrollTo({ top: 0, behavior: 'smooth' })"
            />
        </div>
    </div>

    @if ($this->pendingApprovals === [])
        @include('livewire.chats.show.message-input')
    @else
        <div
            wire:key="pending-approvals"
            wire:loading.remove
            wire:target="approveToolCalls, rejectToolCalls"
            class="motion-safe:animate-message-in mx-auto w-full max-w-3xl shrink-0"
        >
            <x-chat.tool-approvals :approvals="$this->pendingApprovals" />
        </div>
    @endif

    @if ($isEmpty)
        <div wire:key="suggestions" class="mx-auto w-full max-w-3xl divide-y divide-zinc-200 px-2 dark:divide-white/10">
            @foreach ([__('Add 12.5 and 30 with the sum tool'), __('Change my profile name to Ada Lovelace'), __('Explain Livewire islands in three sentences')] as $suggestion)
                <div class="py-1">
                    <flux:button
                        variant="ghost"
                        icon="arrow-up-right"
                        class="w-full justify-start! font-normal!"
                        x-on:click="
                            $wire.newMessage = {{ Js::from($suggestion) }};
                            $wire.sendMessage()
                        "
                    >
                        {{ $suggestion }}
                    </flux:button>
                </div>
            @endforeach
        </div>
    @endif
</div>
