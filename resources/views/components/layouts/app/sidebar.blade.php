<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 me-5 rtl:space-x-reverse" wire:navigate>
            <x-app-logo />
        </a>

        <flux:navlist variant="outline">
            <livewire:chat.sidebar />
        </flux:navlist>

        <flux:spacer />

        <flux:navlist variant="outline">
            <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit"
                target="_blank">
                {{ __('Repository') }}
            </flux:navlist.item>

            <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire"
                target="_blank">
                {{ __('Documentation') }}
            </flux:navlist.item>
        </flux:navlist>

        <!-- Desktop User Menu -->
        <flux:dropdown position="bottom" align="start">
            <flux:profile name="{{ auth()->user()->name ?? 'Guest' }}"
                initials="{{ auth()->user()?->initials() ?? 'G' }}" icon-trailing="chevrons-up-down" />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex gap-2 items-center py-1.5 px-1 text-sm text-start">
                            <span class="flex overflow-hidden relative w-8 h-8 rounded-lg shrink-0">
                                <span
                                    class="flex justify-center items-center w-full h-full text-black rounded-lg dark:text-white bg-neutral-200 dark:bg-neutral-700">
                                    {{ auth()->user()?->initials() ?? 'G' }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-sm leading-tight text-start">
                                @auth
                                    <span class="font-semibold truncate">{{ auth()->user()->name }}</span>
                                    <span class="text-xs truncate">{{ auth()->user()->email }}</span>
                                    @elseauth
                                    <span class="font-semibold truncate">{{ __('Guest') }}</span>
                                @endauth
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                @auth
                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
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
                        <div class="flex gap-2 items-center py-1.5 px-1 text-sm text-start">
                            <span class="flex overflow-hidden relative w-8 h-8 rounded-lg shrink-0">
                                <span
                                    class="flex justify-center items-center w-full h-full text-black rounded-lg dark:text-white bg-neutral-200 dark:bg-neutral-700">
                                    {{ auth()->user()?->initials() ?? 'G' }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-sm leading-tight text-start">
                                @auth
                                    <span class="font-semibold truncate">{{ auth()->user()->name }}</span>
                                    <span class="text-xs truncate">{{ auth()->user()->email }}</span>
                                    @elseauth
                                    <span class="font-semibold truncate">{{ __('Guest') }}</span>
                                @endauth
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                @auth
                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
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
