@props(['message', 'isFirst' => false])

<div class="flex flex-row w-full justify-start @if ($isFirst) flex-1 @endif">
    <div class="bg-zinc-50 dark:bg-zinc-700 prose prose-sm border border-zinc-200 dark:border-zinc-600 rounded-xl max-w-fit min-w-24 p-4 space-y-2 max-h-fit">
        <flux:heading>
            AI
        </flux:heading>
        <flux:text class="prose-zinc prose-sm">
            {!! Str::markdown($message->content) !!}
        </flux:text>
    </div>
</div>
