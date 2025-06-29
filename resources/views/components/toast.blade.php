{{--
    This component is inspired by
    https://devdojo.com/pines and styled
    in a way that looks consitent with FluxUI
--}}

@props([
    'position' => 'bottom-right',
])

@php
    $positionClasses = match ($position) {
        'top-left' => 'top-0 left-0 items-start sm:mt-6 sm:ml-6',
        'top-center' => 'top-0 left-1/2 -translate-x-1/2 items-center sm:mt-6',
        'top-right' => 'top-0 right-0 items-end sm:mt-6 sm:mr-6',
        'bottom-left' => 'bottom-0 left-0 items-start sm:mb-6 sm:ml-6',
        'bottom-center' => 'bottom-0 left-1/2 -translate-x-1/2 items-center sm:mb-6',
        default => 'right-0 bottom-0 items-end sm:mr-6 sm:mb-6',
    };
@endphp

<div
    x-data="{
        toasts: [],
        nextId: 1,
        add(options = {}) {
            const id = this.nextId++
            const defaults = {
                id,
                variant: 'default',
                title: '',
                description: '',
                icon: null,
                dismissible: true,
                duration: 5000,
            }
            console.log(options)
            const toast = { ...defaults, ...options }
            this.toasts.push(toast)
            return id
        },
        remove(id) {
            this.toasts = this.toasts.filter((t) => t.id !== id)
        },
        success(title, description = '', options = {}) {
            return this.add({
                ...options,
                variant: 'success',
                title,
                description,
                icon: options.icon || 'check-circle',
            })
        },
        error(title, description = '', options = {}) {
            return this.add({
                ...options,
                variant: 'error',
                title,
                description,
                icon: options.icon || 'x-circle',
            })
        },
        warning(title, description = '', options = {}) {
            return this.add({
                ...options,
                variant: 'warning',
                title,
                description,
                icon: options.icon || 'exclamation-triangle',
            })
        },
        info(title, description = '', options = {}) {
            return this.add({
                ...options,
                variant: 'info',
                title,
                description,
                icon: options.icon || 'information-circle',
            })
        },
        getVariantClasses(variant) {
            return 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 border border-zinc-200 dark:border-zinc-600'
        },
        getIconColorClasses(variant) {
            switch (variant) {
                case 'success':
                    return 'text-green-600 dark:text-green-400'
                case 'error':
                case 'danger':
                    return 'text-red-600 dark:text-red-400'
                case 'warning':
                    return 'text-yellow-600 dark:text-yellow-400'
                case 'info':
                    return 'text-blue-600 dark:text-blue-400'
                default:
                    return 'text-zinc-600 dark:text-zinc-400'
            }
        },
    }"
    @toast.window="add($event.detail)"
    class="{{ $positionClasses }} pointer-events-none fixed z-50 flex w-full flex-col space-y-4 p-4 sm:max-w-xs sm:p-6"
    {{ $attributes }}
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-data="{
                show: false,
                timer: null,
                toast: toast,
                init() {
                    this.show = true
                    if (this.toast.duration > 0) {
                        this.timer = setTimeout(() => this.dismiss(), this.toast.duration)
                    }
                },
                dismiss() {
                    this.show = false
                    if (this.timer) {
                        clearTimeout(this.timer)
                    }
                    setTimeout(() => remove(this.toast.id), 300)
                },
                pauseTimer() {
                    if (this.timer) {
                        clearTimeout(this.timer)
                    }
                },
                resumeTimer() {
                    if (this.toast.duration > 0) {
                        this.timer = setTimeout(() => this.dismiss(), this.toast.duration)
                    }
                },
            }"
            x-show="show"
            x-transition:enter="transition duration-300 ease-out"
            x-transition:enter-start="scale-90 transform opacity-0"
            x-transition:enter-end="scale-100 transform opacity-100"
            x-transition:leave="transition duration-200 ease-in"
            x-transition:leave-start="scale-100 transform opacity-100"
            x-transition:leave-end="scale-90 transform opacity-0"
            @mouseenter="pauseTimer()"
            @mouseleave="resumeTimer()"
            :class="'pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg shadow-xs dark:shadow-none ' + getVariantClasses(toast.variant)"
            role="alert"
        >
            <div class="p-4">
                <div class="flex items-start">
                    <template x-if="toast.icon">
                        <div class="flex-shrink-0" :class="getIconColorClasses(toast.variant)">
                            <template x-if="toast.icon === 'check-circle'">
                                <flux:icon name="check-circle" class="h-5 w-5" />
                            </template>
                            <template x-if="toast.icon === 'x-circle'">
                                <flux:icon name="x-circle" class="h-5 w-5" />
                            </template>
                            <template x-if="toast.icon === 'exclamation-triangle'">
                                <flux:icon name="exclamation-triangle" class="h-5 w-5" />
                            </template>
                            <template x-if="toast.icon === 'information-circle'">
                                <flux:icon name="information-circle" class="h-5 w-5" />
                            </template>
                        </div>
                    </template>
                    <div class="ml-3 w-0 flex-1" :class="{ 'ml-0': !toast.icon }">
                        <template x-if="toast.title">
                            <p class="text-sm font-medium" x-text="toast.title"></p>
                        </template>
                        <template x-if="toast.description">
                            <p class="mt-1 text-sm opacity-90" x-text="toast.description"></p>
                        </template>
                    </div>
                    <template x-if="toast.dismissible">
                        <div class="ml-4 flex flex-shrink-0">
                            <button
                                @click="dismiss()"
                                type="button"
                                class="focus:ring-accent inline-flex rounded-md hover:opacity-70 focus:ring-2 focus:ring-offset-2 focus:outline-none"
                            >
                                <span class="sr-only">Close</span>
                                <flux:icon name="x-mark" class="h-5 w-5" />
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>
