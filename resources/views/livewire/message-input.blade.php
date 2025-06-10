<div class="flex absolute right-0 bottom-0 left-0 flex-row justify-between items-center">
    <div class="flex flex-row gap-2 w-full">
        <flux:input wire:keydown.enter="sendMessage" wire:model="newMessage" type="text" class=""
            placeholder="Type your message here...">
            <x-slot name="iconTrailing">
                <flux:button size="sm" variant="subtle" icon="paper-airplane" class="-mr-1"
                    wire:click="sendMessage" />
            </x-slot>
        </flux:input>
    </div>
</div>
