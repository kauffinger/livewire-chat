@props(['status'])

@if ($status)
    <div role="status" {{ $attributes->merge(['class' => 'text-sm font-medium text-green-600 dark:text-green-400']) }}>
        {{ $status }}
    </div>
@endif
