@props([
    'message' => null, 
    'isFirst' => false, 
    'isLoading' => false,
    'wireStream' => null,
    'wireReplace' => false
])

<div class="@if ($isFirst) flex-1 @endif flex w-full flex-row justify-start">
    <x-chat.message-content
        :raw-content="$isLoading ? '' : json_encode($message->parts)"
        :wire-stream="$wireStream"
        :wire-replace="$wireReplace"
        :show-article="!$isLoading"
    >
        @if ($isLoading)
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
        @endif
    </x-chat.message-content>
</div>
