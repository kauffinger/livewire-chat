<?php

namespace App\Livewire\Chats;

use App\Actions\AddNewUserMessageToChat;
use App\Actions\PersistStreamDataToMessages;
use App\Actions\UpdateStreamDataFromPrismChunk;
use App\Dtos\StreamData;
use App\Enums\Visibility;
use App\Models\Chat as ChatModel;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Tool;
use Prism\Prism\Prism;
use Prism\Prism\Text\PendingRequest;

class Show extends Component
{
    public ChatModel $chat;

    public string $newMessage = '';

    public string $model = 'gpt-4o-mini';

    public function mount(ChatModel $chat): void
    {
        Gate::authorize('view', $chat);

        $this->chat = $chat;

        $this->model = $this->chat->model ?? 'gpt-4o-mini';
    }

    #[Computed]
    public function messages(): array
    {
        return $this->chat->messages()->oldest()
            ->get()
            ->all();
    }

    public function sendMessage(AddNewUserMessageToChat $addNewUserMessageToChat): void
    {
        Gate::authorize('update', $this->chat);

        $userMessage = trim($this->newMessage);

        if ($userMessage === '') {
            return;
        }

        $addNewUserMessageToChat->handle($this->chat, $userMessage);

        $this->newMessage = '';

        $this->js('$wire.runChatToolLoop()');
    }

    public function runChatToolLoop(
        PersistStreamDataToMessages $persistStreamDataToMessages,
        UpdateStreamDataFromPrismChunk $updateStreamDataFromPrismChunk,
    ): void {
        Gate::authorize('update', $this->chat);

        $generator = Prism::text()
            ->using(Provider::OpenAI, $this->model)
            ->withSystemPrompt('You are a helpful assistant.')
            ->withMessages(collect($this->messages)->map->toPrism()->all())
            ->withMaxSteps(5)
            ->withTools([
                Tool::as('sum')->withNumberParameter('a', 'The first number to sum')
                    ->withNumberParameter('b', 'The second number to sum')
                    ->for('Sums two numbers together')
                    ->using(fn ($a, $b) => (string) ($a + $b)),
            ])
            ->whenProvider(Provider::OpenAI, fn (PendingRequest $request) => match ($this->model) {
                'gpt-4o',
                'gpt-4o-mini',
                'gpt-3.5-turbo',
                'gpt-4',
                'gpt-4.1-nano-2025-04-14' => $request,
                default => $request->withProviderOptions([
                    'reasoning' => ['effort' => 'low', 'summary' => 'detailed'],
                ]),
            })
            ->asStream();

        $streamData = new StreamData;

        foreach ($generator as $chunk) {

            $updateStreamDataFromPrismChunk->handle($streamData, $chunk);

            $this->stream(
                'streamed-message',
                json_encode([...$streamData->toArray(), 'currentChunkType' => $chunk->chunkType->value]),
                true
            );
        }

        $persistStreamDataToMessages->handle($this->chat, $streamData);

        unset($this->messages);
    }

    public function share(): void
    {
        Gate::authorize('update', $this->chat);

        $this->chat->update([
            'visibility' => Visibility::Public->value,
        ]);

        Flux::modal('confirm-share')->close();
    }

    public function unshare(): void
    {
        Gate::authorize('update', $this->chat);

        $this->chat->update([
            'visibility' => Visibility::Private->value,
        ]);

        Flux::modal('confirm-unshare')->close();
    }

    public function setModel(string $value): void
    {
        Gate::authorize('update', $this->chat);

        $this->model = $value;

        $this->chat->update([
            'model' => $value,
        ]);
    }

    public function render(): View
    {
        return view('livewire.chats.show');
    }
}
