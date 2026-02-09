<div class="relative mx-auto h-full max-w-3xl">
    <div
        class="mx-auto flex h-[calc(100vh-10rem)] max-h-screen max-w-3xl flex-1 flex-col-reverse gap-4 overflow-y-scroll py-1 lg:h-[calc(100vh-8rem)]"
        x-data
        x-init="$el.scrollTop = $el.scrollHeight"
    >
        <div
            wire:loading.flex
            wire:target="runAgent"
            wire:key="loading-{{ count($this->messages) }}"
            class="relative mx-auto hidden w-full max-w-4xl flex-1"
        >
            <x-chat.assistant-message :is-loading="true" wire-stream="streamed-message" :wire-replace="true" />
        </div>

        @if ($newMessage)
            <x-chat.user-message :message="['role' => 'user', 'content' => $newMessage]" :index="-1" />
        @endif

        @foreach (array_reverse($this->messages) as $message)
            @if ($message['role'] === 'user')
                <x-chat.user-message :message="$message" :index="$loop->index" />
            @elseif ($message['role'] === 'assistant')
                <x-chat.assistant-message :message="$message" :is-first="$loop->first" />
            @endif
        @endforeach

        @auth
            @include('livewire.chats.show.message-input')
        @endauth
    </div>
</div>
