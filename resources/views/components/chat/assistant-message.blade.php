@props([
    'message' => ['content' => '', 'reasoning' => '', 'toolCalls' => []],
    'streaming' => false,
])

@php
    $disclosureClasses = 'flex items-center gap-2 rounded-md text-sm text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white';
    $detailClasses = 'min-w-0 font-mono text-[0.8125rem] leading-6 break-words whitespace-pre-wrap text-zinc-800 dark:text-zinc-200';
@endphp

{{-- Alpine reads the message once, so a stored message that changes, like a turn resumed after an approval, needs a new key. --}}
<div
    wire:key="{{ md5(json_encode($message)) }}"
    class="w-full space-y-3"
    x-data="assistantMessage(@js($message), @js(['copy' => __('Copy'), 'copied' => __('Copied'), 'thinking' => __('Thinking…'), 'runningTool' => __('Running :tool…')]), @js($streaming))"
    x-on:click="copyCode($event)"
>
    <div x-show="message.reasoning.trim() !== ''">
        <button
            type="button"
            class="{{ $disclosureClasses }}"
            x-on:click="showReasoning = ! showReasoning"
            x-bind:aria-expanded="showReasoning"
        >
            <flux:icon.light-bulb variant="mini" />
            <span>{{ __('Reasoning') }}</span>
            <flux:icon.chevron-right
                variant="micro"
                class="transition-transform"
                x-bind:class="showReasoning && 'rotate-90'"
            />
        </button>

        <div x-show="showReasoning" x-collapse>
            <article
                wire:ignore
                class="prose prose-sm prose-zinc prose-chat dark:prose-invert max-w-none pt-2 text-zinc-500 dark:text-zinc-400"
                x-html="reasoningHtml"
            ></article>
        </div>
    </div>

    <div x-show="message.toolCalls.length > 0" class="space-y-2">
        <template x-for="toolCall in message.toolCalls" :key="toolCall.id">
            <div x-data="{ isOpen: false }">
                <button
                    type="button"
                    class="{{ $disclosureClasses }}"
                    x-on:click="isOpen = ! isOpen"
                    x-bind:aria-expanded="isOpen"
                >
                    @if ($streaming)
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

    <article
        wire:ignore
        class="prose prose-zinc prose-chat dark:prose-invert max-w-none min-w-0"
        x-ref="reply"
        x-show="message.content.trim() !== ''"
        @if ($streaming)
            data-reveal
        @endif
    ></article>

    @if ($streaming)
        <div
            role="status"
            class="flex min-w-0 items-center gap-2.5 text-zinc-500 dark:text-zinc-400"
            x-show="! isWriting() || message.content.trim() === ''"
        >
            <span class="motion-safe:animate-status-pulse size-2.5 shrink-0 rounded-full bg-current"></span>
            <template x-for="label in [status()]" :key="label">
                <span class="motion-safe:animate-status-in shrink-0 font-medium" x-text="label"></span>
            </template>
            <span class="min-w-0 truncate" x-show="latestThought() !== ''" x-text="latestThought()"></span>
        </div>
    @endif

    @if ($message['failed'] ?? false)
        <flux:callout
            variant="danger"
            icon="exclamation-triangle"
            :heading="__('The assistant could not finish this response.')"
        />
    @endif

    @unless ($streaming)
        <flux:button
            size="xs"
            variant="ghost"
            square
            class="-ms-1 text-zinc-400! hover:text-zinc-800! dark:hover:text-white!"
            :tooltip="__('Copy')"
            aria-label="{{ __('Copy') }}"
            x-show="message.content.trim() !== ''"
            x-on:click="copy()"
        >
            <flux:icon.clipboard variant="micro" x-show="! copied" />
            <flux:icon.check variant="micro" x-show="copied" class="text-green-600 dark:text-green-400" />
        </flux:button>
    @endunless
</div>
