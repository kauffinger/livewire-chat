@props(['message'])

<div class="flex w-full justify-end">
    <div class="max-w-[85%] rounded-2xl bg-zinc-100 px-4 py-2.5 dark:bg-zinc-700">
        <flux:text class="break-words text-zinc-800 dark:text-white">
            {!! nl2br(e($message['content'])) !!}
        </flux:text>
    </div>
</div>
