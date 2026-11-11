<div class="flex flex-col gap-6">
    <x-auth-header
        :title="__('Confirm password')"
        :description="__('This is a secure area of the application. Please confirm your password before continuing.')"
    />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="confirmPassword" class="flex flex-col gap-6">
        <flux:input
            wire:model="password"
            :label="__('Password')"
            type="password"
            required
            autofocus
            autocomplete="current-password"
            :placeholder="__('Password')"
            viewable
        />

        <flux:button variant="primary" type="submit" class="w-full">{{ __('Confirm') }}</flux:button>
    </form>
</div>
