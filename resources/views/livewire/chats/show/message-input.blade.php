<div class="relative mx-auto w-full max-w-3xl shrink-0" x-data>
    <flux:textarea
        wire:model="newMessage"
        rows="auto"
        resize="none"
        class="max-h-48 pe-14 focus:border-zinc-500! focus:ring-1! focus:ring-zinc-500! focus:ring-offset-0! dark:focus:border-zinc-400! dark:focus:ring-zinc-400!"
        aria-label="{{ __('Message') }}"
        placeholder="{{ __('Type your message here...') }}"
        x-init="matchMedia('(pointer: fine)').matches && $el.focus()"
        x-on:keydown.enter="
            if (! $event.shiftKey && ! $event.isComposing) {
                $event.preventDefault();
                $refs.send.click();
            }
        "
    />

    <div class="absolute end-2 bottom-2">
        <flux:button
            x-ref="send"
            size="sm"
            variant="primary"
            icon="paper-airplane"
            :tooltip="__('Send')"
            tooltip:kbd="Enter"
            aria-label="{{ __('Send') }}"
            wire:click="sendMessage"
            x-bind:disabled="$wire.newMessage.trim() === '' || $wire.pendingMessage !== ''"
        />
    </div>
</div>
