<div class="relative mx-auto w-full max-w-3xl shrink-0" x-data>
    <flux:textarea
        wire:model="newMessage"
        rows="auto"
        resize="none"
        class="max-h-48 pe-14"
        placeholder="{{ __('Type your message here...') }}"
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
            wire:click="sendMessage"
            wire:loading.attr="disabled"
            wire:target="sendMessage, runAgent"
        />
    </div>
</div>
