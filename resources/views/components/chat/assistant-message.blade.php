@props(['message', 'isFirst' => false])

<div class="flex flex-row w-full justify-start @if ($isFirst) flex-1 @endif">
    <div
        class="p-4 space-y-2 rounded-xl border bg-zinc-50 prose prose-sm border-zinc-200 max-w-fit min-w-24 max-h-fit dark:bg-zinc-700 dark:border-zinc-600">
        <flux:heading>
            AI
        </flux:heading>
        <flux:text x-data="streamedMarkdown()">
            <span x-ref="raw" class="hidden">{{ $message->content }}</span>
            <article wire:ignore
                class="overflow-hidden min-w-0 max-w-none break-words prose prose-sm prose-zinc prose-p:m-0 prose-code:font-mono prose-pre:border prose-pre:border-zinc-200 prose-pre:dark:border-zinc-600 prose-pre:rounded-md prose-pre:p-4 prose-pre:mb-1 prose-pre:bg-zinc-100 prose-pre:dark:bg-zinc-800 prose-pre:text-zinc-900 prose-pre:dark:text-zinc-100 dark:prose-invert"
                x-html="html"></article>
        </flux:text>
    </div>
</div>
