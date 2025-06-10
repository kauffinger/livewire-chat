@props(['title', 'description'])

<div class="flex flex-col w-full text-center">
    <flux:heading size="xl">{{ $title }}</flux:heading>
    <flux:subheading>{{ $description }}</flux:subheading>
</div>
