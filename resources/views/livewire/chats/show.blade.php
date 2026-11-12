<div
    class="flex h-[calc(100dvh-6.5rem)] flex-col gap-4 data-empty:justify-center data-empty:gap-8 data-empty:pb-[12dvh] lg:h-[calc(100dvh-4rem)]"
    @if ($conversationId === null)
        data-empty
        x-bind:data-empty="$wire.pendingMessage === ''"
    @endif
>
    @if ($conversationId === null)
        {{-- flux:heading leaves a Livewire block marker behind it. Outside this wrapper, the marker makes the morph re-insert the message input, which drops its focus. --}}
        <div wire:key="greeting" class="hidden in-data-empty:block">
            <flux:heading level="1" class="text-center text-3xl! font-medium! tracking-tight text-balance sm:text-4xl!">
                {{ __('What can I help you with?') }}
            </flux:heading>
        </div>
    @endif

    <div
        wire:key="conversation"
        class="relative flex min-h-0 flex-1 in-data-empty:hidden"
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

                <div
                    wire:key="pending-user-message"
                    wire:show="pendingMessage !== ''"
                    class="motion-safe:animate-message-in"
                    style="display: none"
                >
                    <x-chat.user-message wire:text="pendingMessage" class="whitespace-pre-line" />
                </div>

                <div
                    wire:loading.block
                    wire:target="runAgent, approveToolCalls, rejectToolCalls"
                    wire:key="streaming-message"
                    class="motion-safe:animate-message-in hidden"
                >
                    <x-chat.assistant-message streaming />
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

    @if ($conversationId === null)
        <div
            wire:key="suggestions"
            class="mx-auto hidden w-full max-w-3xl divide-y divide-zinc-200 px-2 in-data-empty:block dark:divide-white/10"
        >
            @foreach ([__('Add 12.5 and 30 with the sum tool'), __('Change my profile name to Ada Lovelace'), __('Explain Livewire islands in three sentences')] as $suggestion)
                <div class="py-1">
                    <flux:button
                        variant="ghost"
                        icon="arrow-up-right"
                        class="w-full justify-start! font-normal!"
                        x-on:click="
                            $wire.newMessage = {{ Js::from($suggestion) }};
                            $wire.$js.send()
                        "
                    >
                        {{ $suggestion }}
                    </flux:button>
                </div>
            @endforeach
        </div>
    @endif
</div>

@script
    <script>
        let cancelReply = () => {};

        $wire.$intercept('runAgent', ({ action }) => {
            cancelReply = () => action.cancel();
        });

        $js.send = () => {
            const send = () => {
                $wire.pendingMessage = $wire.newMessage.trim();
                $wire.newMessage = '';
                $wire.runAgent();
            };

            if ($wire.conversationId === null && document.startViewTransition) {
                document.startViewTransition(send);
            } else {
                send();
            }

            matchMedia('(pointer: fine)').matches && $wire.$refs.input.focus();
        };

        $js.stop = () => {
            cancelReply();

            $wire.newMessage = $wire.pendingMessage;
            $wire.pendingMessage = '';
            $wire.$refs.input.focus();
        };
    </script>
@endscript
