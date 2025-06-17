<div class="absolute right-0 bottom-0 left-0 flex flex-row items-center justify-between">
    <div class="flex w-full flex-row gap-2">
        <flux:input
            wire:keydown.enter="sendMessage"
            wire:model="newMessage"
            type="text"
            class=""
            placeholder="Type your message here..."
        >
            <x-slot name="iconTrailing">
                <flux:button size="sm" variant="subtle" icon="paper-airplane" class="-mr-1" wire:click="sendMessage" />
            </x-slot>
        </flux:input>
    </div>
</div>
