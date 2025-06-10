<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen antialiased bg-neutral-100 dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
    <div class="flex flex-col gap-6 justify-center items-center p-6 md:p-10 bg-muted min-h-svh">
        <div class="flex flex-col gap-6 w-full max-w-md">
            <a href="{{ route('home') }}" class="flex flex-col gap-2 items-center font-medium" wire:navigate>
                <span class="flex justify-center items-center w-9 h-9 rounded-md">
                    <x-app-logo-icon class="text-black fill-current dark:text-white size-9" />
                </span>

                <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
            </a>

            <div class="flex flex-col gap-6">
                <div
                    class="bg-white rounded-xl border text-stone-800 shadow-xs dark:bg-stone-950 dark:border-stone-800">
                    <div class="py-8 px-10">{{ $slot }}</div>
                </div>
            </div>
        </div>
    </div>
    @fluxScripts
</body>

</html>
