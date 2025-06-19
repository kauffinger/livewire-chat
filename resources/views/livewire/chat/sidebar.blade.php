<div x-data @chat-started.window="$wire.$refresh()">
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
                    :current="request()->routeIs('chat.show') && request()->route('chat')?->id === $chat['id']"
                    wire:key="$chat['id']"
                    wire:navigate.hover
                >
                    {{ $chat['title'] }}
                </flux:navlist.item>
            @endforeach
        </div>
    </flux:navlist.group>
</div>
