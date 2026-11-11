<flux:menu class="w-[220px]">
    <div class="flex items-center gap-2 px-1 py-1.5">
        <flux:avatar size="sm" :initials="auth()->user()->initials()" />

        <div class="grid flex-1 text-start text-sm leading-tight">
            <span class="truncate font-semibold text-zinc-800 dark:text-white">{{ auth()->user()->name }}</span>
            <span class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ auth()->user()->email }}</span>
        </div>
    </div>

    <flux:menu.separator />

    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
        {{ __('Settings') }}
    </flux:menu.item>

    <flux:menu.separator />

    <form method="POST" action="{{ route('logout') }}" class="w-full">
        @csrf
        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
            {{ __('Log out') }}
        </flux:menu.item>
    </form>
</flux:menu>
