<div class="mx-auto w-full max-w-3xl space-y-6 pb-6">
    <div class="flex min-h-10 items-center justify-between gap-4">
        <flux:heading size="xl" level="1">{{ __('Conversations') }}</flux:heading>

        <flux:button
            :href="route('conversation.create')"
            wire:navigate
            icon="plus"
            variant="primary"
            class="max-lg:hidden"
        >
            {{ __('New conversation') }}
        </flux:button>
    </div>

    <flux:input
        wire:model.live.debounce.250ms="search"
        type="search"
        icon="magnifying-glass"
        aria-label="{{ __('Search conversations') }}"
        placeholder="{{ __('Search by title') }}"
        x-init="matchMedia('(pointer: fine)').matches && $el.focus()"
    />

    @if ($conversations->isNotEmpty())
        <ul class="divide-y divide-zinc-200 dark:divide-white/10">
            @foreach ($conversations as $conversation)
                <li wire:key="conversation-{{ $conversation->id }}" class="py-1">
                    <a
                        href="{{ route('conversation.show', $conversation->id) }}"
                        wire:navigate.hover
                        class="flex items-center gap-4 rounded-lg px-3 py-2.5 hover:bg-zinc-800/5 dark:hover:bg-white/[7%]"
                    >
                        <span class="min-w-0 flex-1 truncate font-medium text-zinc-800 dark:text-white">
                            {{ $conversation->title }}
                        </span>

                        <time
                            datetime="{{ $conversation->updated_at->toIso8601String() }}"
                            class="shrink-0 text-sm text-zinc-500 dark:text-zinc-400"
                        >
                            {{ $conversation->updated_at->diffForHumans() }}
                        </time>
                    </a>
                </li>
            @endforeach
        </ul>

        {{ $conversations->links() }}
    @elseif ($search !== '')
        <div class="py-16 text-center">
            <flux:heading size="lg" level="2">{{ __('No conversations found') }}</flux:heading>
            <flux:text class="mt-1">{{ __('No title contains “:search”.', ['search' => $search]) }}</flux:text>

            <flux:button wire:click="$set('search', '')" class="mt-6">{{ __('Clear search') }}</flux:button>
        </div>
    @else
        <div class="py-16 text-center">
            <flux:heading size="lg" level="2">{{ __('No conversations yet') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Every conversation you start is kept here.') }}</flux:text>
        </div>
    @endif
</div>
