<div class="mx-auto max-w-7xl pb-6 xl:px-24">
    <div>
        <div class="mb-6 flex items-center justify-between">
            <flux:heading size="xl" level="1">
                {{ __('All Chats') }}
            </flux:heading>

            <flux:button wire:click="createNewChat" icon="plus" variant="primary">
                {{ __('New Chat') }}
            </flux:button>
        </div>

        @if ($chats->count() > 0)
            <div class="space-y-4">
                @foreach ($chats as $chat)
                    <div
                        class="rounded-lg border border-zinc-200 bg-white p-6 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <flux:heading size="lg" level="3" class="mb-2">
                                    <flux:link
                                        :href="route('chat.show', $chat->id)"
                                        wire:navigate.hover
                                        class="hover:underline"
                                    >
                                        {{ $chat->title }}
                                    </flux:link>
                                </flux:heading>

                                <div class="flex flex-wrap gap-4 text-sm text-zinc-500 dark:text-zinc-400">
                                    <span class="flex items-center">
                                        <flux:icon name="clock" class="mr-1 h-4 w-4" />
                                        {{ $chat->created_at->diffForHumans() }}
                                    </span>
                                    <span class="flex items-center">
                                        <flux:icon name="arrow-path" class="mr-1 h-4 w-4" />
                                        {{ $chat->updated_at->diffForHumans() }}
                                    </span>
                                    <span class="flex items-center">
                                        <flux:icon name="chat-bubble-left" class="mr-1 h-4 w-4" />
                                        {{ $chat->messages_count }}
                                    </span>
                                </div>
                            </div>

                            <flux:button
                                :href="route('chat.show', $chat->id)"
                                wire:navigate.hover
                                size="sm"
                                variant="ghost"
                                icon="arrow-right"
                            >
                                {{ __('Open') }}
                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $chats->links() }}
            </div>
        @else
            <div class="py-12 text-center">
                <flux:icon name="chat-bubble-left" class="mx-auto mb-4 h-16 w-16 text-zinc-300 dark:text-zinc-600" />
                <flux:heading size="lg" level="2" class="mb-2 text-zinc-500 dark:text-zinc-400">
                    {{ __('No chats yet') }}
                </flux:heading>
                <flux:text class="mb-6 text-zinc-400 dark:text-zinc-500">
                    {{ __('Start your first conversation by creating a new chat.') }}
                </flux:text>
                <flux:button wire:click="createNewChat" icon="plus" variant="primary">
                    {{ __('Create Your First Chat') }}
                </flux:button>
            </div>
        @endif
    </div>
</div>
