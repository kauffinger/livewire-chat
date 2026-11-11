<x-layouts.auth.simple :title="__('No access')">
    <x-auth-header :title="__('No access')" :description="__('This page belongs to someone else.')" />

    <flux:button variant="primary" :href="route('home')" class="w-full">
        {{ __('Back to your conversations') }}
    </flux:button>
</x-layouts.auth.simple>
