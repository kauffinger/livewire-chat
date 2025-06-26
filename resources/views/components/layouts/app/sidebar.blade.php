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

            <!-- Fixed header with logo -->
            <div class="flex-shrink-0">
                <a
                    href="{{ route('dashboard') }}"
                    class="me-5 flex items-center space-x-2 rtl:space-x-reverse"
                    wire:navigate
                >
                    <x-app-logo />
                </a>
            </div>

            <!-- Scrollable chat section -->
            <div class="min-h-0 flex-1 overflow-hidden">
                <flux:navlist variant="outline" class="h-full">
                    <livewire:chat-sidebar />
                </flux:navlist>
            </div>

            <!-- Fixed footer with links and user menu -->
            <div class="flex-shrink-0">
                <flux:navlist variant="outline">
                    <flux:navlist.item
                        icon="folder-git-2"
                        href="https://github.com/laravel/livewire-starter-kit"
                        target="_blank"
                    >
                        {{ __('Repository') }}
                    </flux:navlist.item>

                    <flux:navlist.item
                        icon="book-open-text"
                        href="https://laravel.com/docs/starter-kits#livewire"
                        target="_blank"
                    >
                        {{ __('Documentation') }}
                    </flux:navlist.item>
                </flux:navlist>
            </div>

            <!-- Desktop User Menu -->
            <flux:dropdown position="bottom" align="start">
                <flux:profile
                    name="{{ auth()->user()->name ?? 'Guest' }}"
                    initials="{{ auth()->user()?->initials() ?? 'G' }}"
                    icon-trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()?->initials() ?? 'G' }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    @auth
                                        <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                        <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                    @elseauth
                                        <span class="truncate font-semibold">{{ __('Guest') }}</span>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    @auth
                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item
                                as="button"
                                type="submit"
                                icon="arrow-right-start-on-rectangle"
                                class="w-full"
                            >
                                {{ __('Log Out') }}
                            </flux:menu.item>
                        </form>
                    @endauth
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile initials="{{ auth()->user()?->initials() ?? 'G' }}" icon-trailing="chevron-down" />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()?->initials() ?? 'G' }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    @auth
                                        <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                        <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                    @elseauth
                                        <span class="truncate font-semibold">{{ __('Guest') }}</span>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    @auth
                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item
                                as="button"
                                type="submit"
                                icon="arrow-right-start-on-rectangle"
                                class="w-full"
                            >
                                {{ __('Log Out') }}
                            </flux:menu.item>
                        </form>
                    @endauth
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @livewireScriptConfig
        @fluxScripts
    </body>
</html>
