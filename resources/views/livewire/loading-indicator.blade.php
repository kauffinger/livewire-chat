<div wire:loading wire:target="runChatToolLoop" class="flex relative flex-1 mx-auto w-full max-w-4xl">
    <div class="flex flex-row w-full justify-start">
        <div class="bg-zinc-50 dark:bg-zinc-700 prose prose-sm border border-zinc-200 dark:border-zinc-600 rounded-xl max-w-fit min-w-24 p-4 space-y-2 max-h-fit">
            <flux:heading>
                AI
            </flux:heading>
            <flux:text>
                <span class="p-0 m-0 prose-zinc dark:prose-invert prose-sm" wire:stream="streamed-message" wire:replace>
                    <span class="animate-pulse">...</span>
                </span>
            </flux:text>
        </div>
    </div>
</div>
