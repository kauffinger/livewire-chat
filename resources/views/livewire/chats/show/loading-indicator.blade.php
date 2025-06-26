<div
    wire:loading.flex
    wire:target="runChatToolLoop"
    wire:key="loading-{{ count($this->messages) }}"
    class="relative mx-auto hidden w-full max-w-4xl flex-1"
>
    <div class="flex w-full flex-row justify-start">
        <x-chat.message-content wire-stream="streamed-message" :wire-replace="true" :show-article="false">
            <!-- Thinking indicator -->
            <div x-show="isCurrentlyThinking()" class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                <div class="flex space-x-1">
                    <div class="h-1 w-1 animate-bounce rounded-full bg-zinc-400 [animation-delay:-0.3s]"></div>
                    <div class="h-1 w-1 animate-bounce rounded-full bg-zinc-400 [animation-delay:-0.15s]"></div>
                    <div class="h-1 w-1 animate-bounce rounded-full bg-zinc-400"></div>
                </div>
                <span class="text-sm">Thinking...</span>
            </div>

            <!-- Tool usage indicator -->
            <div x-show="isCurrentlyUsingTools()" class="flex items-center gap-2 text-blue-500 dark:text-blue-400">
                <flux:icon.wrench-screwdriver class="h-4 w-4 animate-spin" />
                <span class="text-sm">Using tools...</span>
            </div>
        </x-chat.message-content>
    </div>
</div>
