@if ($showCreate)
    <div class="flex min-h-screen items-center justify-center">
        <form wire:submit.prevent="createNewConversation" class="space-y-4 text-center">
            <flux:heading size="xl" level="1" class="mb-6">
                {{ __('Welcome!') }}
            </flux:heading>
            <flux:button type="submit" icon="plus" variant="primary">
                {{ __('Start a new conversation') }}
            </flux:button>
        </form>
    </div>
@endif
