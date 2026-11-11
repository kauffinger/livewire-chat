<div class="flex flex-col gap-6">
    <x-auth-header
        :title="__('Verify your email')"
        :description="__('Please verify your email address by clicking on the link we just emailed to you.')"
    />

    @if (session('status') == 'verification-link-sent')
        <x-auth-session-status
            class="text-center"
            :status="__('A new verification link has been sent to the email address you provided during registration.')"
        />
    @endif

    <div class="flex flex-col items-center gap-3">
        <flux:button wire:click="sendVerification" variant="primary" class="w-full">
            {{ __('Resend verification email') }}
        </flux:button>

        <flux:link class="cursor-pointer text-sm" wire:click="logout">
            {{ __('Log out') }}
        </flux:link>
    </div>
</div>
