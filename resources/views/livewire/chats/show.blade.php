<div class="relative mx-auto h-full max-w-3xl">
    <div
        class="mx-auto flex h-[calc(100vh-10rem)] max-h-screen max-w-3xl flex-1 flex-col-reverse overflow-y-scroll lg:h-[calc(100vh-8rem)]"
        x-data
        x-init="$el.scrollTop = $el.scrollHeight"
    >
        <div class="mb-auto flex flex-col gap-4 py-1">
            @foreach ($this->messages as $message)
                <div wire:key="message-{{ $message['id'] }}">
                    @if ($message['role'] === 'user')
                        <x-chat.user-message :message="$message" :index="$message['id']" />
                    @elseif ($message['role'] === 'assistant')
                        <x-chat.assistant-message :message="$message" />
                    @endif
                </div>
            @endforeach

            @if ($pendingMessage !== '')
                <div wire:key="pending-user-message">
                    <x-chat.user-message :message="['content' => $pendingMessage]" index="pending" />
                </div>
            @endif

            <div
                wire:loading.flex
                wire:target="runAgent"
                wire:key="streaming-message-{{ count($this->messages) }}"
                class="relative mx-auto hidden w-full max-w-4xl"
            >
                <x-chat.assistant-message :is-loading="true" wire-stream="streamed-message" :wire-replace="true" />
            </div>
        </div>

        @include('livewire.chats.show.message-input')
    </div>
</div>
