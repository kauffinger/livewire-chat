@props(['message', 'index'])

<div class="flex flex-row justify-end w-full" id="user-message-{{ $index }}">
    <div
        class="p-4 space-y-2 rounded-xl border bg-zinc-50 border-zinc-200 max-w-fit min-w-24 dark:bg-zinc-700 dark:border-zinc-600">
        <flux:heading>
            You
        </flux:heading>
        <flux:text>
            {{ $message->content }}
        </flux:text>
    </div>
</div>
