@props(['message'])

<div class="flex w-full justify-end">
    <div class="max-w-[85%] rounded-3xl bg-zinc-100 px-5 py-3 dark:bg-zinc-700">
        <flux:text size="lg" class="leading-7 break-words text-zinc-800 dark:text-white">
            {!! nl2br(e($message['content'])) !!}
        </flux:text>
    </div>
</div>
