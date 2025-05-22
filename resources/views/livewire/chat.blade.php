<div class="relative h-full">
    <div
        class="flex h-[calc(100vh-8rem)] max-h-screen max-w-3xl mx-auto flex-1 flex-col-reverse overflow-y-scroll gap-4 rounded-xl">
        <div wire:loading wire:target="runChatToolLoop" class="flex relative flex-1 mx-auto w-full max-w-4xl">
            <div class="flex flex-row w-full justify-start">
                <div class="bg-zinc-50 prose prose-sm border border-zinc-200 rounded-xl max-w-fit min-w-24 p-4 space-y-2 max-h-fit">
                    <flux:heading>
                        AI
                    </flux:heading>
                    <flux:text>
                        <span class="p-0 m-0 prose prose-sm" wire:stream="streamed-message" wire:replace>
                            <span class="animate-pulse">...</span>
                        </span>
                    </flux:text>
                </div>
            </div>
        </div>
        @foreach (array_reverse($messages) as $message)
            @if ($message instanceof \App\Dtos\UserMessage)
                <div class="flex flex-row w-full justify-end" id="user-message-{{ $loop->index }}">
                    <div class="bg-zinc-50 prose prose-sm border border-zinc-200 rounded-xl max-w-fit min-w-24 p-4 space-y-2">
                        <flux:heading>
                            You
                        </flux:heading>
                        <flux:text>
                            {{ $message->content }}
                        </flux:text>
                    </div>
                </div>
            @elseif($message instanceof \App\Dtos\AssistantMessage)
                <div class="flex flex-row w-full justify-start @if ($loop->first) flex-1 @endif">
                    <div class="bg-zinc-50 prose prose-sm border border-zinc-200 rounded-xl max-w-fit min-w-24 p-4 space-y-2 max-h-fit">
                        <flux:heading>
                            AI
                        </flux:heading>
                        <flux:text class="prose prose-sm">
                            {!! Str::markdown($message->content) !!}
                        </flux:text>
                    </div>
                </div>
            @endif
        @endforeach

        <div class="absolute bottom-0 left-0 right-0 flex flex-row justify-between items-center p-4">
            <div class="flex flex-row gap-2 w-full">
                <flux:input wire:keydown.enter="sendMessage" wire:model="newMessage" type="text" class="" placeholder="Type your message here..." >
                    <x-slot name="iconTrailing">
                        <flux:button size="sm" variant="subtle" icon="paper-airplane" class="-mr-1" wire:click="sendMessage" />
                    </x-slot>
                </flux:input>
            </div>
        </div>

    </div>
</div>
