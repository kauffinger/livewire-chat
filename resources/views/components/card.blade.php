@props(['class' => ''])

<div
    {{ $attributes->merge()->class(['rounded-lg border border-zinc-200 bg-white p-6 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700']) }}
>
    {{ $slot }}
</div>
