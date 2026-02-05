@props(['message', 'index'])

<div class="flex w-full flex-row justify-end" id="user-message-{{ $index }}">
    <div
        class="max-w-fit min-w-24 space-y-2 rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-600 dark:bg-zinc-700"
    >
        <flux:heading>You</flux:heading>
        <flux:text>
            {{ is_array($message) ? ($message['content'] ?? '') : ($message->content ?? $message->parts['text'] ?? '') }}
        </flux:text>
    </div>
</div>
