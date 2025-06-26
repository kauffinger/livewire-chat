@props(['message'])

<div class="flex w-full flex-row justify-start">
    <div
        class="prose prose-sm max-h-fit max-w-fit min-w-24 space-y-2 rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-600 dark:bg-green-700/20"
    >
        <flux:heading>Tool Results</flux:heading>

        <div x-data="{
            toolResults: @js($message->toolResults ?? []),
        }">
            <!-- Tool results display -->
            <div class="space-y-2">
                <template
                    x-for="result in toolResults"
                    :key="result.toolCallId + (result.toolCallResultId || Date.now())"
                >
                    <div class="border-b border-green-200 pb-2 last:border-b-0 dark:border-green-600">
                        <div class="mb-1 flex items-center gap-2">
                            <flux:icon.check-circle class="h-4 w-4 text-green-600 dark:text-green-400" />
                            <span
                                x-text="result.toolName"
                                class="font-mono text-sm font-medium text-green-700 dark:text-green-300"
                            ></span>
                        </div>
                        <div class="pl-6 text-xs text-green-600 dark:text-green-400">
                            <div
                                x-text="
                                    typeof result.result === 'string'
                                        ? result.result
                                        : JSON.stringify(result.result)
                                "
                                class="break-words"
                            ></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
