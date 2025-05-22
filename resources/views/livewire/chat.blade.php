<div class="relative h-full max-w-3xl mx-auto">
    <div
        class="flex h-[calc(100vh-10rem)] lg:h-[calc(100vh-8rem)] max-h-screen max-w-3xl mx-auto flex-1 flex-col-reverse overflow-y-scroll gap-4 py-1">

        @include('livewire.loading-indicator')

        @foreach (array_reverse($messages) as $message)
            @if ($message instanceof \App\Dtos\UserMessage)
                <x-chat.user-message :message="$message" :index="$loop->index" />
            @elseif($message instanceof \App\Dtos\AssistantMessage)
                <x-chat.assistant-message :message="$message" :is-first="$loop->first" />
            @endif
        @endforeach

        <x-chat.message-input />

    </div>
</div>
