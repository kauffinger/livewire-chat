@props(['approvals'])

<div
    class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-700/50"
    role="group"
    aria-label="{{ __('Approval needed') }}"
>
    <div class="flex items-start gap-3">
        <flux:icon.shield-exclamation class="size-6 shrink-0 text-amber-600 dark:text-amber-400" />

        <div class="min-w-0 flex-1 space-y-4">
            <flux:heading size="lg">{{ __('Approval needed') }}</flux:heading>

            @foreach ($approvals as $approval)
                <div wire:key="approval-{{ $approval->id }}" class="space-y-2">
                    @if ($approval->reason)
                        <flux:text size="lg">{{ $approval->reason }}</flux:text>
                    @endif

                    <dl
                        class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 rounded-xl bg-zinc-100 px-4 py-3 text-sm dark:bg-zinc-900/40"
                    >
                        <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Tool') }}</dt>
                        <dd class="min-w-0 font-mono break-words text-zinc-800 dark:text-white">
                            {{ $approval->tool }}
                        </dd>

                        @foreach ($approval->arguments as $name => $value)
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ $name }}</dt>
                            <dd class="min-w-0 break-words text-zinc-800 dark:text-white">
                                {{ is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE) }}
                            </dd>
                        @endforeach
                    </dl>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-5 flex justify-end gap-2">
        <flux:button variant="ghost" wire:click="rejectToolCalls">
            {{ __('Reject') }}
        </flux:button>
        <flux:button variant="primary" wire:click="approveToolCalls">
            {{ __('Approve') }}
        </flux:button>
    </div>
</div>
