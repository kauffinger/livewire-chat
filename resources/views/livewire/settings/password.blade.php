<x-settings.section
    :heading="__('Password')"
    :description="__('Ensure your account is using a long, random password to stay secure')"
>
    <form wire:submit="updatePassword" class="space-y-6">
        <flux:input
            wire:model="current_password"
            :label="__('Current password')"
            type="password"
            required
            autocomplete="current-password"
            viewable
        />
        <flux:input
            wire:model="password"
            :label="__('New password')"
            type="password"
            required
            autocomplete="new-password"
            viewable
        />
        <flux:input
            wire:model="password_confirmation"
            :label="__('Confirm new password')"
            type="password"
            required
            autocomplete="new-password"
            viewable
        />

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>

            <x-action-message on="password-updated" />
        </div>
    </form>
</x-settings.section>
