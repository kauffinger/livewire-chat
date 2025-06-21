@props(['message', 'isFirst' => false])

<div class="@if ($isFirst) flex-1 @endif flex w-full flex-row justify-start">
    <div
        class="prose prose-sm max-h-fit max-w-fit min-w-24 space-y-2 rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-600 dark:bg-zinc-700"
        x-data="streamedMarkdown()"
        x-init="
            // Initialize with static message data (tool calls only, no tool results)
            streamData.toolCalls = @js($message->toolCalls ?? []);
        "
    >
        <flux:heading>AI</flux:heading>

        <!-- Tool calls display -->
        <div x-show="hasToolCalls()" class="border-t border-zinc-200 pt-2 dark:border-zinc-600">
            <template x-for="toolCall in streamData.toolCalls" :key="toolCall.name + Date.now()">
                <div class="mt-1 flex items-center gap-2 text-xs">
                    <flux:icon.wrench-screwdriver class="h-3 w-3 text-blue-500" />
                    <span x-text="toolCall.name" class="font-mono text-blue-600 dark:text-blue-400"></span>
                </div>
            </template>
        </div>

        <!-- Main content -->
        <flux:text>
            <span x-ref="raw" class="hidden">{{ $message->content }}</span>
            <article
                wire:ignore
                class="prose prose-zinc prose-sm prose-p:m-0 prose-code:font-mono prose-pre:border prose-pre:border-zinc-200 prose-pre:dark:border-zinc-600 prose-pre:rounded-md prose-pre:p-4 prose-pre:mb-1 prose-pre:bg-zinc-100 prose-pre:dark:bg-zinc-800 prose-pre:text-zinc-900 prose-pre:dark:text-zinc-100 dark:prose-invert max-w-none min-w-0 overflow-hidden break-words"
                x-html="html"
            ></article>
        </flux:text>
    </div>
</div>
