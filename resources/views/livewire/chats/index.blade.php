<div class="mx-auto max-w-7xl pb-6 xl:px-24">
    <div>
        <div class="mb-6 flex items-center justify-between">
            <flux:heading size="xl" level="1">
                {{ __('All Conversations') }}
            </flux:heading>

            <flux:button wire:click="createNewConversation" icon="plus" variant="primary">
                {{ __('New Conversation') }}
            </flux:button>
        </div>

        @if ($conversations->count() > 0)
            <div class="space-y-4">
                @foreach ($conversations as $conversation)
                    <x-card>
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <flux:heading size="lg" level="3" class="mb-2">
                                    <flux:link
                                        :href="route('conversation.show', $conversation->id)"
                                        wire:navigate.hover
                                        class="hover:underline"
                                    >
                                        {{ $conversation->title }}
                                    </flux:link>
                                </flux:heading>

                                <div class="flex flex-wrap gap-4 text-sm text-zinc-500 dark:text-zinc-400">
                                    <span class="flex items-center">
                                        <flux:icon name="clock" class="mr-1 h-4 w-4" />
                                        {{ $conversation->created_at->diffForHumans() }}
                                    </span>
                                    <span class="flex items-center">
                                        <flux:icon name="arrow-path" class="mr-1 h-4 w-4" />
                                        {{ $conversation->updated_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>

                            <flux:button
                                :href="route('conversation.show', $conversation->id)"
                                wire:navigate.hover
                                size="sm"
                                variant="ghost"
                                icon="arrow-right"
                            >
                                {{ __('Open') }}
                            </flux:button>
                        </div>
                    </x-card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $conversations->links() }}
            </div>
        @else
            <div class="py-12 text-center">
                <flux:icon name="chat-bubble-left" class="mx-auto mb-4 h-16 w-16 text-zinc-300 dark:text-zinc-600" />
                <flux:heading size="lg" level="2" class="mb-2 text-zinc-500 dark:text-zinc-400">
                    {{ __('No conversations yet') }}
                </flux:heading>
                <flux:text class="mb-6 text-zinc-400 dark:text-zinc-500">
                    {{ __('Start your first conversation by creating a new one.') }}
                </flux:text>
                <flux:button wire:click="createNewConversation" icon="plus" variant="primary">
                    {{ __('Create Your First Conversation') }}
                </flux:button>
            </div>
        @endif
    </div>
</div>
