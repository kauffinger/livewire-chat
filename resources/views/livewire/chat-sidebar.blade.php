<div class="space-y-4">
    <flux:button icon="plus" size="sm" class="w-full" :href="route('conversation.create')" wire:navigate>
        {{ __('New conversation') }}
    </flux:button>

    @if ($this->conversations !== [])
        <flux:navlist.group :heading="__('Recent')">
            @foreach ($this->conversations as $conversation)
                <flux:navlist.item
                    variant="outline"
                    :href="route('conversation.show', $conversation['id'])"
                    :current="$activeConversationId === $conversation['id']"
                    :title="$conversation['title']"
                    class="*:min-w-0"
                    wire:key="conversation-{{ $conversation['id'] }}"
                    wire:navigate
                >
                    <span class="block truncate leading-5">{{ $conversation['title'] }}</span>
                </flux:navlist.item>
            @endforeach
        </flux:navlist.group>
    @endif

    @auth
        <flux:navlist.item
            variant="outline"
            :href="route('conversations.index')"
            icon="list-bullet"
            wire:navigate.hover
        >
            {{ __('All conversations') }}
        </flux:navlist.item>
    @endauth
</div>
