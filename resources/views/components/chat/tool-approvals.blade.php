@props(['approvals'])

<div class="flex w-full flex-row justify-start">
    <div
        class="max-w-fit min-w-24 space-y-3 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-600 dark:bg-amber-700/20"
    >
        <flux:heading>{{ __('Approval needed') }}</flux:heading>

        @foreach ($approvals as $approval)
            <div wire:key="approval-{{ $approval->id }}" class="space-y-1">
                <div class="flex items-center gap-2 text-xs">
                    <flux:icon.wrench-screwdriver class="h-3 w-3 text-amber-600 dark:text-amber-400" />
                    <span class="font-mono text-amber-700 dark:text-amber-300">{{ $approval->tool }}</span>
                </div>

                @if ($approval->reason)
                    <flux:text>{{ $approval->reason }}</flux:text>
                @endif

                <pre
                    class="rounded bg-amber-100 p-2 text-xs break-words whitespace-pre-wrap text-amber-900 dark:bg-amber-900/40 dark:text-amber-100"
                >
{{ json_encode($approval->arguments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @endforeach

        <div class="flex gap-2">
            <flux:button size="sm" variant="primary" wire:click="approveToolCalls">
                {{ __('Approve') }}
            </flux:button>
            <flux:button size="sm" wire:click="rejectToolCalls">
                {{ __('Reject') }}
            </flux:button>
        </div>
    </div>
</div>
