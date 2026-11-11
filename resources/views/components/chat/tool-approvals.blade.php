@props(['approvals'])

<flux:callout variant="warning" icon="shield-exclamation">
    <flux:callout.heading>{{ __('Approval needed') }}</flux:callout.heading>

    @foreach ($approvals as $approval)
        <div wire:key="approval-{{ $approval->id }}" class="space-y-2">
            <flux:badge size="sm" color="yellow" icon="wrench-screwdriver">{{ $approval->tool }}</flux:badge>

            @if ($approval->reason)
                <flux:callout.text>{{ $approval->reason }}</flux:callout.text>
            @endif

            <pre
                class="rounded-md bg-yellow-400/20 p-2 text-xs break-words whitespace-pre-wrap text-yellow-900 dark:text-yellow-100"
            >
{{ json_encode($approval->arguments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    @endforeach

    <x-slot name="actions">
        <flux:button size="sm" variant="primary" wire:click="approveToolCalls">
            {{ __('Approve') }}
        </flux:button>
        <flux:button size="sm" variant="ghost" wire:click="rejectToolCalls">
            {{ __('Reject') }}
        </flux:button>
    </x-slot>
</flux:callout>
