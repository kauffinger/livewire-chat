<div
    wire:key="message-input"
    @if ($conversationId === null)
        style="view-transition-name: message-input"
    @endif
    class="mx-auto w-full max-w-3xl shrink-0"
>
    <div
        class="cursor-text rounded-3xl border border-zinc-200 bg-white shadow-sm focus-within:border-zinc-400 focus-within:shadow-md dark:border-white/10 dark:bg-zinc-700/50 dark:focus-within:border-white/30"
        x-on:click="$event.target.closest('button') || $wire.$refs.input.focus()"
    >
        <flux:textarea
            wire:ref="input"
            wire:model="newMessage"
            rows="auto"
            resize="none"
            class="max-h-60 border-0! bg-transparent! px-5! pt-4! pb-2! text-base! shadow-none! focus:ring-0! focus:ring-offset-0!"
            aria-label="{{ __('Message') }}"
            placeholder="{{ __('Ask anything') }}"
            x-init="matchMedia('(pointer: fine)').matches && $el.focus()"
            x-on:keydown.enter="
                if (! $event.shiftKey && ! $event.isComposing) {
                    $event.preventDefault();
                    $wire.$refs.send.click();
                }
            "
        />

        <div class="flex items-center justify-between gap-3 ps-5 pe-3 pb-3">
            <flux:text
                size="sm"
                class="max-sm:invisible pointer-coarse:invisible"
                style="display: none"
                x-show="$wire.newMessage !== ''"
                x-transition.opacity
            >
                {{ __('Enter to send, Shift + Enter for a new line') }}
            </flux:text>

            <div class="ms-auto flex shrink-0">
                <flux:button
                    wire:ref="send"
                    size="sm"
                    variant="primary"
                    icon="arrow-up"
                    class="rounded-full!"
                    :tooltip="__('Send')"
                    tooltip:kbd="Enter"
                    aria-label="{{ __('Send') }}"
                    x-show="$wire.pendingMessage === ''"
                    x-on:click="$wire.$js.send()"
                    x-bind:disabled="$wire.newMessage.trim() === '' || $wire.pendingMessage !== ''"
                />

                <flux:button
                    size="sm"
                    variant="primary"
                    icon="stop"
                    class="rounded-full!"
                    style="display: none"
                    :tooltip="__('Stop')"
                    aria-label="{{ __('Stop') }}"
                    x-show="$wire.pendingMessage !== ''"
                    x-on:click="$wire.$js.stop()"
                />
            </div>
        </div>
    </div>
</div>
