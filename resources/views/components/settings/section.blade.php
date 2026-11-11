@props(['heading', 'description'])

<section {{ $attributes->class('grid gap-x-10 gap-y-6 py-10 md:grid-cols-3') }}>
    <div>
        <flux:heading size="lg" level="2">{{ $heading }}</flux:heading>
        <flux:subheading>{{ $description }}</flux:subheading>
    </div>

    <div class="md:col-span-2">
        {{ $slot }}
    </div>
</section>
