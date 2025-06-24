<div x-data>
    <flux:navlist.group heading="{{ __('Chats') }}">
        <!-- New Chat button - fixed at top -->
        <div class="mb-2 flex-shrink-0">
            @auth
                <flux:button icon="plus" size="sm" class="w-full hover:cursor-pointer" wire:click="createNewChat">
                    {{ __('New Chat') }}
                </flux:button>
            @elseguest
                <a href="{{ route('login') }}" wire:navigate>
                    <flux:button icon="plus" size="sm" class="w-full hover:cursor-pointer">
                        {{ __('New Chat') }}
                    </flux:button>
                </a>
            @endauth
        </div>

        <!-- Scrollable chat list -->
        <div class="min-h-0 space-y-1">
            @foreach ($this->chats as $chat)
                <flux:navlist.item
                    :href="route('chat.show', $chat['id'])"
                    icon="layout-grid"
                    :current="$activeChatId === $chat['id']"
                    wire:key="$chat['id']"
                    wire:navigate.hover
                >
                    {{ $chat['title'] }}
                </flux:navlist.item>
            @endforeach
        </div>

        <!-- Show All button -->
        @auth
            <div class="mt-2 border-t border-zinc-200 pt-2 dark:border-zinc-700">
                <flux:button
                    :href="route('chats.index')"
                    wire:navigate.hover
                    icon="list-bullet"
                    size="sm"
                    variant="ghost"
                    class="w-full justify-start"
                >
                    {{ __('Show All Chats') }}
                </flux:button>
            </div>
        @endauth
    </flux:navlist.group>
</div>
