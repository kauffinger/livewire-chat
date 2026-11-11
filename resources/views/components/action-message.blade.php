@props(['on'])

<div
    role="status"
    x-data="{ shown: false, timeout: null }"
    x-init="@this.on('{{ $on }}', () => {
    clearTimeout(timeout);
    shown = true;
    timeout = setTimeout(() => { shown = false }, 2000);
})"
    x-show="shown"
    x-transition:leave.opacity.duration.1500ms
    style="display: none"
    {{ $attributes->merge(['class' => 'text-sm text-zinc-500 dark:text-zinc-400']) }}
>
    {{ $slot->isEmpty() ? __('Saved.') : $slot }}
</div>
