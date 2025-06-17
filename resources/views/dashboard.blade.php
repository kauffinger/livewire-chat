<x-layouts.app :title="__('Dashboard')">
    @if (($showCreateButton ?? false) === true)
        <div class="flex min-h-screen items-center justify-center">
            <form method="POST" action="{{ route('chat.store') }}" class="space-y-4 text-center">
                @csrf
                <flux:heading size="xl" level="1" class="mb-6">
                    {{ __('Welcome!') }}
                </flux:heading>
                <flux:button icon="plus" variant="primary">
                    {{ __('Start a new chat') }}
                </flux:button>
            </form>
        </div>
    @else
        <livewire:chat :chat="$chat ?? null" />
    @endif
</x-layouts.app>
