<div class="flex min-h-0 flex-1 flex-col gap-4" x-on:livewire:navigated.document="$wire.refresh()">
    <flux:sidebar.header>
        <flux:sidebar.brand :href="route('dashboard')" name="Livewire Chat Kit" wire:navigate>
            <x-slot:logo class="bg-accent-content size-6 rounded-md">
                <x-app-logo-icon class="size-4 text-white dark:text-black" />
            </x-slot>
        </flux:sidebar.brand>

        <flux:sidebar.collapse
            class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-me-2"
        />
    </flux:sidebar.header>

    {{-- Livewire marks the current link in the browser. wire:ignore.self keeps a re-render of the sidebar from removing that mark. --}}
    <flux:sidebar.nav>
        <flux:sidebar.item
            icon="plus"
            :href="route('conversation.create')"
            wire:navigate
            wire:current.exact=""
            wire:ignore.self
        >
            {{ __('New conversation') }}
        </flux:sidebar.item>

        @auth
            <flux:sidebar.item
                icon="magnifying-glass"
                :href="route('conversations.index')"
                wire:navigate.hover
                wire:current.exact=""
                wire:ignore.self
            >
                {{ __('Search conversations') }}
            </flux:sidebar.item>
        @endauth
    </flux:sidebar.nav>

    <div
        class="-mx-4 min-h-0 flex-1 overflow-y-auto px-4 in-data-flux-sidebar-collapsed-desktop:mx-0 in-data-flux-sidebar-collapsed-desktop:px-0"
        wire:navigate:scroll
    >
        <flux:sidebar.nav class="gap-4">
            @foreach ($this->conversationGroups as $label => $conversations)
                <flux:sidebar.group :heading="$label" wire:key="group-{{ $label }}">
                    @foreach ($conversations as $conversation)
                        <flux:sidebar.item
                            :href="route('conversation.show', $conversation->id)"
                            wire:key="conversation-{{ $conversation->id }}"
                            wire:navigate.hover
                            wire:current.exact=""
                            wire:ignore.self
                        >
                            {{ $conversation->title }}
                        </flux:sidebar.item>
                    @endforeach
                </flux:sidebar.group>
            @endforeach
        </flux:sidebar.nav>
    </div>

    @auth
        <flux:dropdown position="top" align="start">
            <flux:sidebar.profile
                :name="auth()->user()->name"
                :initials="auth()->user()->initials()"
                icon:trailing="chevrons-up-down"
            />

            <x-user-menu />
        </flux:dropdown>
    @endauth
</div>
