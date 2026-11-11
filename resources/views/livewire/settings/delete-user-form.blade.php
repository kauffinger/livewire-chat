<section class="mt-10 space-y-6 border-t border-zinc-200 pt-10 dark:border-white/10">
    <div>
        <flux:heading size="lg" level="2">{{ __('Delete account') }}</flux:heading>
        <flux:subheading>{{ __('Delete your account and all of its resources') }}</flux:subheading>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button variant="danger">{{ __('Delete account') }}</flux:button>
    </flux:modal.trigger>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Are you sure you want to delete your account?') }}</flux:heading>

                <flux:subheading>
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                </flux:subheading>
            </div>

            <flux:input wire:model="password" :label="__('Password')" type="password" viewable />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">{{ __('Delete account') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
