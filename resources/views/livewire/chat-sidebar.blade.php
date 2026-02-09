<div x-data>
    <flux:navlist.group heading="{{ __('Conversations') }}">
        <!-- New Conversation button - fixed at top -->
        <div class="mb-2 flex-shrink-0">
            @auth
                <flux:button icon="plus" size="sm" class="w-full hover:cursor-pointer" wire:click="createNewConversation">
                    {{ __('New Conversation') }}
                </flux:button>
            @elseguest
                <a href="{{ route('login') }}" wire:navigate>
                    <flux:button icon="plus" size="sm" class="w-full hover:cursor-pointer">
                        {{ __('New Conversation') }}
                    </flux:button>
                </a>
            @endauth
        </div>

        <!-- Scrollable conversation list -->
        <div class="min-h-0 space-y-1">
            @foreach ($this->conversations as $conversation)
                <flux:navlist.item
                    :href="route('conversation.show', $conversation['id'])"
                    icon="layout-grid"
                    :current="$activeConversationId === $conversation['id']"
                    wire:key="$conversation['id']"
                    wire:navigate
                >
                    {{ $conversation['title'] }}
                </flux:navlist.item>
            @endforeach
        </div>

        <!-- Show All button -->
        @auth
            <div class="mt-2 border-t border-zinc-200 pt-2 dark:border-zinc-700">
                <flux:button
                    :href="route('conversations.index')"
                    wire:navigate.hover
                    icon="list-bullet"
                    size="sm"
                    variant="ghost"
                    class="w-full justify-start"
                >
                    {{ __('Show All Conversations') }}
                </flux:button>
            </div>
        @endauth
    </flux:navlist.group>
</div>
