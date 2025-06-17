<flux:navlist.group heading="{{ __('Chats') }}" class="grid">
    <!-- New Chat button -->
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

    <!-- List of chats -->
    @foreach ($chats as $chat)
        <flux:navlist.item
            :href="route('chat.show', $chat['id'])"
            icon="layout-grid"
            :current="request()->routeIs('chat.show') && request()->route('chat')?->id === $chat['id']"
            wire:navigate.hover
        >
            {{ $chat['title'] }}
        </flux:navlist.item>
    @endforeach
</flux:navlist.group>
