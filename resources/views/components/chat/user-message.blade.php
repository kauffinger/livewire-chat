@props(['message', 'index'])

<div class="flex flex-row w-full justify-end" id="user-message-{{ $index }}">
    <div class="bg-zinc-50 prose prose-sm border border-zinc-200 rounded-xl max-w-fit min-w-24 p-4 space-y-2">
        <flux:heading>
            You
        </flux:heading>
        <flux:text>
            {{ $message->content }}
        </flux:text>
    </div>
</div>