<div wire:loading.flex wire:target="runChatToolLoop" class="hidden relative flex-1 mx-auto w-full max-w-4xl">
    <div class="flex flex-row w-full justify-start">
        <div class="bg-zinc-50 dark:bg-zinc-700 prose prose-sm border border-zinc-200 dark:border-zinc-600 rounded-xl max-w-fit min-w-24 p-4 space-y-2 max-h-fit">
            <flux:heading>
                AI
            </flux:heading>
            <flux:text x-data="streamedMarkdown()" >
                <span x-ref="raw" class="hidden" wire:stream="streamed-message" wire:replace></span>
                <article wire:ignore class="prose prose-zinc prose-sm dark:prose-invert max-w-none min-w-0 overflow-hidden break-words prose-p:m-0 prose-code:font-mono prose-pre:border prose-pre:border-zinc-200 prose-pre:dark:border-zinc-600 prose-pre:rounded-md prose-pre:p-4 prose-pre:mb-1 prose-pre:bg-zinc-100 prose-pre:dark:bg-zinc-800" x-html="html"></article>
            </flux:text>
        </div>
    </div>
</div>
