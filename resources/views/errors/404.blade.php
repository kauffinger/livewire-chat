<x-layouts.auth.simple :title="__('Page not found')">
    <x-auth-header :title="__('Page not found')" :description="__('This page does not exist, or it was moved.')" />

    <flux:button variant="primary" :href="route('home')" class="w-full">
        {{ __('Back to your conversations') }}
    </flux:button>
</x-layouts.auth.simple>
