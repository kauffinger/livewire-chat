@props([
    'message' => [],
    'isLoading' => false,
    'wireStream' => null,
    'wireReplace' => false,
])

@php
    $disclosureClasses = 'flex items-center gap-2 rounded-md text-sm text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white';
    $detailClasses = 'min-w-0 font-mono text-[0.8125rem] leading-6 break-words whitespace-pre-wrap text-zinc-800 dark:text-zinc-200';
@endphp

<div
    class="w-full space-y-3"
    x-data="markdownProcessor({ copy: @js(__('Copy')), copied: @js(__('Copied')) })"
    x-on:click="copyCode($event)"
>
    <div x-show="hasThinking()">
        <button
            type="button"
            class="{{ $disclosureClasses }}"
            x-on:click="toggleThinking()"
            x-bind:aria-expanded="showThinking"
        >
            <flux:icon.light-bulb variant="mini" />
            <span>{{ __('Reasoning') }}</span>
            <flux:icon.chevron-right
                variant="micro"
                class="transition-transform"
                x-bind:class="showThinking && 'rotate-90'"
            />
        </button>

        <div x-show="showThinking" x-collapse>
            <article
                wire:ignore
                class="prose prose-sm prose-zinc prose-chat dark:prose-invert max-w-none pt-2 text-zinc-500 dark:text-zinc-400"
                x-html="thinkingHtml"
            ></article>
        </div>
    </div>

    <div x-show="hasToolCalls()" class="space-y-2">
        <template x-for="toolCall in streamData.toolCalls" :key="toolCall.id">
            <div x-data="{ isOpen: false }">
                <button
                    type="button"
                    class="{{ $disclosureClasses }}"
                    x-on:click="isOpen = ! isOpen"
                    x-bind:aria-expanded="isOpen"
                >
                    @if ($isLoading)
                        <flux:icon.loading class="size-5" x-show="! ('result' in toolCall)" />
                    @else
                        <flux:icon.wrench-screwdriver variant="mini" x-show="! ('result' in toolCall)" />
                    @endif
                    <flux:icon.check-circle
                        variant="mini"
                        class="text-green-600 dark:text-green-400"
                        x-show="'result' in toolCall && ! toolCall.denied && ! toolCall.failed"
                    />
                    <flux:icon.x-circle
                        variant="mini"
                        class="text-red-600 dark:text-red-400"
                        x-show="toolCall.denied || toolCall.failed"
                    />
                    <span class="font-mono" x-text="toolCall.name"></span>
                    <flux:icon.chevron-right
                        variant="micro"
                        class="transition-transform"
                        x-bind:class="isOpen && 'rotate-90'"
                    />
                </button>

                <div x-show="isOpen" x-collapse>
                    <dl
                        class="mt-2 grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 rounded-xl bg-zinc-100 px-4 py-3 text-sm dark:bg-zinc-900/40"
                    >
                        <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Arguments') }}</dt>
                        <dd class="{{ $detailClasses }}" x-text="JSON.stringify(toolCall.arguments)"></dd>

                        <dt class="text-zinc-500 dark:text-zinc-400" x-show="'result' in toolCall">
                            {{ __('Result') }}
                        </dt>
                        <dd
                            class="{{ $detailClasses }}"
                            x-show="'result' in toolCall"
                            x-text="formatToolResult(toolCall.result)"
                        ></dd>
                    </dl>
                </div>
            </div>
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
        class="prose prose-zinc prose-chat dark:prose-invert max-w-none min-w-0"
        x-html="html"
        x-show="html.length > 0"
    ></article>

    @if ($isLoading)
        <div role="status" class="flex items-center gap-2.5 text-zinc-500 dark:text-zinc-400" x-show="! isWriting()">
            <span class="motion-safe:animate-status-pulse size-2.5 rounded-full bg-current"></span>
            <span x-show="! isCurrentlyUsingTools()">{{ __('Thinking…') }}</span>
            <span x-show="isCurrentlyUsingTools()">{{ __('Using tools…') }}</span>
        </div>
    @endif

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
