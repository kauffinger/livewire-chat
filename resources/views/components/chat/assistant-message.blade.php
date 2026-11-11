@props([
    'message' => [],
    'isLoading' => false,
    'wireStream' => null,
    'wireReplace' => false,
    'thinkingProseSize' => 'prose-sm',
])

<div class="flex w-full gap-3" x-data="markdownProcessor()">
    <flux:avatar size="sm" icon="sparkles" class="shrink-0" />

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
            <flux:button size="xs" variant="ghost" icon="light-bulb" x-on:click="toggleThinking()">
                {{ __('Reasoning') }}
            </flux:button>

            <div x-show="showThinking" x-collapse class="mt-2">
                <div class="rounded-lg bg-zinc-100 p-3 text-xs dark:bg-zinc-900/50">
                    <article
                        wire:ignore
                        class="prose prose-zinc {{ $thinkingProseSize }} prose-p:m-0 prose-code:font-mono prose-pre:text-xs dark:prose-invert max-w-none break-words"
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
            class="prose prose-zinc prose-sm prose-p:m-0 prose-code:font-mono prose-pre:border prose-pre:border-zinc-200 prose-pre:dark:border-zinc-600 prose-pre:rounded-md prose-pre:p-4 prose-pre:mb-1 prose-pre:bg-zinc-100 prose-pre:dark:bg-zinc-800 prose-pre:text-zinc-900 prose-pre:dark:text-zinc-100 dark:prose-invert max-w-none min-w-0 overflow-hidden break-words"
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
    </div>
</div>
