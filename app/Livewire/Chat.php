<?php

namespace App\Livewire;

use App\Enums\Visibility;
use App\Models\Chat as ChatModel;
use App\Models\Message;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Prism\Prism\Enums\ChunkType;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class Chat extends Component
{
    public array $messages = [];

    public ChatModel $chat;

    public string $newMessage = '';

    public string $model = 'gpt-4o-mini';

    public function mount(ChatModel $chat): void
    {
        $this->chat = $chat;

        $this->model = $this->chat->model ?? 'gpt-4o-mini';

        $this->messages = $this->chat->messages()
            ->orderBy('created_at')
            ->get()
            ->map(function (Message $message) {
                return match ($message->role) {
                    'user' => new \App\Dtos\UserMessage($message->parts['text'] ?? ''),
                    'assistant' => new \App\Dtos\AssistantMessage($message->parts['text'] ?? '', [], []),
                };
            })
            ->all();
    }

    public function sendMessage(): void
    {
        abort_unless(Auth::user()->can('update', $this->chat), 403);

        $userMessage = trim($this->newMessage);

        if ($userMessage === '') {
            return;
        }

        $this->messages[] = new \App\Dtos\UserMessage($userMessage);

        $this->chat->messages()->create([
            'role' => 'user',
            'parts' => [
                'text' => $userMessage,
            ],
            'attachments' => '[]',
        ]);

        $this->chat->update([
            'title' => $this->messages[0]->content,
        ]);

        $this->newMessage = '';

        $this->js('$wire.runChatToolLoop()');
    }

    public function runChatToolLoop(): void
    {
        abort_unless(Auth::user()->can('update', $this->chat), 403);

        $generator = Prism::text()
            ->using(Provider::OpenAI, $this->model)
            ->withSystemPrompt('You are a helpful assistant.')
            ->withMessages(collect($this->messages)->map->toPrism()->all())
            ->asStream();

        $parts = [];
        $fullText = '';

        foreach ($generator as $chunk) {
            $chunkTypeString = $this->mapChunkTypeToString($chunk->chunkType);

            if (! isset($parts[$chunkTypeString])) {
                $parts[$chunkTypeString] = '';
            }

            $parts[$chunkTypeString] .= $chunk->text;

            $fullText .= $chunk->text;

            $this->stream('streamed-message', htmlentities($fullText), true);
        }

        if ($parts !== []) {
            $this->chat->messages()->create([
                'role' => 'assistant',
                'parts' => $parts,
                'attachments' => '[]',
            ]);
            $this->chat->touch();
        }

        $this->messages[] = new \App\Dtos\AssistantMessage($fullText);
    }

    /**
     * Map Prism\Prism\Enums\ChunkType to the string keys used in the messages.parts JSON column.
     */
    private function mapChunkTypeToString(ChunkType $chunkType): string
    {
        return match ($chunkType) {
            ChunkType::Text => 'text',
            ChunkType::Thinking => 'thinking',
            ChunkType::Meta => 'meta',
        };
    }

    public function share(): void
    {
        abort_unless(Auth::user()->can('update', $this->chat), 403);

        $this->chat->update([
            'visibility' => Visibility::Public->value,
        ]);

        Flux::modal('confirm-share')->close();
    }

    public function unshare(): void
    {
        abort_unless(Auth::user()->can('update', $this->chat), 403);

        $this->chat->update([
            'visibility' => Visibility::Private->value,
        ]);

        Flux::modal('confirm-unshare')->close();
    }

    public function setModel(string $value): void
    {
        abort_unless(Auth::user()->can('update', $this->chat), 403);

        $this->model = $value;

        $this->chat->update([
            'model' => $value,
        ]);
    }

    public function render(): View
    {
        return view('livewire.chat');
    }
}
