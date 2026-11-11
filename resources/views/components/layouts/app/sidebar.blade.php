<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>

    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar
            sticky
            stashable
            class="flex h-screen flex-col border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a
                href="{{ route('dashboard') }}"
                class="me-5 flex shrink-0 items-center space-x-2 rtl:space-x-reverse"
                wire:navigate
            >
                <x-app-logo />
            </a>

            <flux:navlist variant="outline" class="min-h-0 flex-1 overflow-y-auto">
                <livewire:chat-sidebar />
            </flux:navlist>

            <flux:navlist variant="outline" class="shrink-0">
                <flux:navlist.item
                    icon="folder-git-2"
                    icon:trailing="arrow-up-right"
                    href="https://github.com/kauffinger/livewire-chat"
                    target="_blank"
                >
                    {{ __('Repository') }}
                </flux:navlist.item>

                <flux:navlist.item
                    icon="book-open-text"
                    icon:trailing="arrow-up-right"
                    href="https://laravel.com/docs/ai-sdk"
                    target="_blank"
                >
                    {{ __('AI SDK documentation') }}
                </flux:navlist.item>
            </flux:navlist>

            <flux:dropdown position="bottom" align="start" class="shrink-0">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevrons-up-down"
                />

                <x-user-menu />
            </flux:dropdown>
        </flux:sidebar>

        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:button
                variant="ghost"
                icon="plus"
                :href="route('conversation.create')"
                aria-label="{{ __('New conversation') }}"
                wire:navigate
            />

            <flux:dropdown position="top" align="end">
                <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

                <x-user-menu />
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast position="top end" />
        @endpersist

        @livewireScriptConfig
        @fluxScripts
    </body>
</html>
