<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>

    <body class="min-h-screen bg-white dark:bg-zinc-800">
        {{-- x-persist in place of @persist: the directive wraps the sidebar in a div, which takes it out of Flux's page grid. A persisted sidebar stays open over the next page on a phone, so it closes itself. --}}
        <flux:sidebar
            x-persist="sidebar"
            x-on:livewire:navigated.document="
                $el.matches('[data-flux-sidebar-on-mobile]:not([data-flux-sidebar-collapsed-mobile])') &&
                    $dispatch('flux-sidebar-toggle')
            "
            sticky
            collapsible
            class="h-dvh border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <livewire:chat-sidebar />
        </flux:sidebar>

        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:button
                variant="subtle"
                icon="plus"
                inset="right"
                :href="route('conversation.create')"
                aria-label="{{ __('New conversation') }}"
                wire:navigate
            />
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast position="top end" />
        @endpersist

        @livewireScriptConfig
        @fluxScripts
    </body>
</html>
