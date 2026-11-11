@props([
    'message' => [],
    'isLoading' => false,
    'wireStream' => null,
    'wireReplace' => false,
    'thinkingProseSize' => 'prose-sm',
])

<div class="flex w-full gap-3" x-data="markdownProcessor()">
    <flux:avatar size="sm" icon="sparkles" class="shrink-0 max-sm:hidden" aria-hidden="true" />

    <div class="min-w-0 flex-1 space-y-2 pt-1">
        @if ($isLoading)
            <flux:skeleton.group animate="shimmer" class="max-w-md" x-show="isWaiting()">
                <flux:skeleton.line />
                <flux:skeleton.line class="w-2/3" />
            </flux:skeleton.group>

            <div x-show="isCurrentlyThinking()" class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                <flux:icon.loading class="size-4" />
                <span class="text-sm">{{ __('Thinking...') }}</span>
            </div>

            <div x-show="isCurrentlyUsingTools()" class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                <flux:icon.loading class="size-4" />
                <span class="text-sm">{{ __('Using tools...') }}</span>
            </div>
        @endif

        <div x-show="hasThinking()">
            <flux:button
                size="xs"
                variant="ghost"
                icon="light-bulb"
                class="-ms-2"
                x-on:click="toggleThinking()"
                x-bind:aria-expanded="showThinking"
            >
                {{ __('Reasoning') }}
            </flux:button>

            <div x-show="showThinking" x-collapse class="mt-2">
                <div class="rounded-lg bg-zinc-100 p-3 text-xs dark:bg-zinc-900/50">
                    <article
                        wire:ignore
                        class="prose prose-zinc {{ $thinkingProseSize }} prose-chat dark:prose-invert"
                        x-html="thinkingHtml"
                    ></article>
                </div>
            </div>
        </div>

        <div x-show="hasToolCalls()" class="flex flex-wrap gap-1.5">
            <template x-for="toolCall in streamData.toolCalls" :key="toolCall.id">
                <flux:badge size="sm" icon="wrench-screwdriver" x-bind:title="JSON.stringify(toolCall.arguments)">
                    <span x-text="toolCall.name" class="font-mono"></span>
                    <flux:icon.check
                        x-show="'result' in toolCall && ! toolCall.denied"
                        variant="micro"
                        class="ms-1 text-green-600 dark:text-green-400"
                    />
                    <flux:icon.x-mark
                        x-show="toolCall.denied"
                        variant="micro"
                        class="ms-1 text-red-600 dark:text-red-400"
                    />
                </flux:badge>
            </template>
        </div>

        <span
            x-ref="raw"
            class="hidden"
            @if ($wireStream)
                wire:stream="{{ $wireStream }}"
            @endif
            @if ($wireReplace)
                wire:replace
            @endif
        >
            {{ $isLoading ? '' : json_encode(['text' => $message['content'] ?? '', 'thinking' => $message['thinking'] ?? '', 'toolCalls' => $message['toolCalls'] ?? []]) }}
        </span>
        <article
            wire:ignore
            class="prose prose-zinc prose-sm prose-chat dark:prose-invert min-w-0"
            x-html="html"
            @if ($isLoading)
                x-show="html.length > 0"
            @endif
        ></article>

        @if ($message['failed'] ?? false)
            <flux:callout
                variant="danger"
                icon="exclamation-triangle"
                :heading="__('The assistant could not finish this response.')"
            />
        @endif

        @unless ($isLoading)
            <flux:button
                size="xs"
                variant="ghost"
                square
                class="-ms-1 text-zinc-400! hover:text-zinc-800! dark:hover:text-white!"
                :tooltip="__('Copy')"
                aria-label="{{ __('Copy') }}"
                x-show="streamData.text.trim() !== ''"
                x-on:click="copy()"
            >
                <flux:icon.clipboard variant="micro" x-show="! copied" />
                <flux:icon.check variant="micro" x-show="copied" class="text-green-600 dark:text-green-400" />
            </flux:button>
        @endunless
    </div>
</div>
