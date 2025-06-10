<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen antialiased bg-white dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
    <div class="flex flex-col gap-6 justify-center items-center p-6 md:p-10 bg-background min-h-svh">
        <div class="flex flex-col gap-2 w-full max-w-sm">
            <a href="{{ route('home') }}" class="flex flex-col gap-2 items-center font-medium" wire:navigate>
                <span class="flex justify-center items-center mb-1 w-9 h-9 rounded-md">
                    <x-app-logo-icon class="text-black fill-current dark:text-white size-9" />
                </span>
                <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
            </a>
            <div class="flex flex-col gap-6">
                {{ $slot }}
            </div>
        </div>
    </div>
    @fluxScripts
</body>

</html>
