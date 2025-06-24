<?php

namespace App\Livewire\Chats;

use App\Enums\Visibility;
use App\Models\Chat as ChatModel;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Prism\Prism\Enums\ChunkType;
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
        $this->chat = $chat;

        $this->model = $this->chat->model ?? 'gpt-4o-mini';
    }

    #[Computed]
    public function messages(): array
    {
        return $this->chat->messages()
            ->orderBy('created_at')
            ->get()
            ->all();
    }

    public function sendMessage(): void
    {
        abort_unless(Auth::user()->can('update', $this->chat), 403);

        $userMessage = trim($this->newMessage);

        if ($userMessage === '') {
            return;
        }

        $this->chat->messages()->create([
            'role' => 'user',
            'parts' => [
                'text' => $userMessage,
            ],
            'attachments' => '[]',
        ]);

        if ($this->chat->messages()->count() === 1) {
            $this->chat->update([
                'title' => $this->messages[0]->parts['text'] ?? '',
            ]);
        }

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
            ->withMaxSteps(5)
            ->withTools([
                Tool::as('sum')->withNumberParameter('a', 'The first number to sum', required: true)
                    ->withNumberParameter('b', 'The second number to sum', required: true)
                    ->for('Sums two numbers together')
                    ->using(function ($a, $b) {
                        return (string) ($a + $b);
                    }),
            ])
            ->whenProvider(Provider::OpenAI, function (PendingRequest $request) {
                return $request->withProviderOptions([
                    'reasoning' => ['effort' => 'low', 'summary' => 'detailed'],
                ]);
            })
            ->asStream();

        $streamData = [
            'text' => '',
            'thinking' => '',
            'meta' => '',
            'toolCalls' => [],
            'toolResults' => [],
            'currentChunkType' => 'text',
        ];

        foreach ($generator as $chunk) {
            switch ($chunk->chunkType) {
                case ChunkType::ToolCall:
                    // Extract tool calls from the chunk's toolCalls array and add to accumulated data
                    foreach ($chunk->toolCalls as $toolCall) {
                        $streamData['toolCalls'][] = [
                            'name' => $toolCall->name ?? 'unknown',
                            'id' => $toolCall->id ?? null,
                            'arguments' => $toolCall->arguments() ?? [],
                            'reasoningId' => $toolCall->reasoningId ?? null,
                            'resultId' => $toolCall->resultId ?? null,
                            'reasoningSummary' => $toolCall->reasoningSummary ?? null,
                        ];
                    }
                    break;
                case ChunkType::ToolResult:
                    // Extract tool results from the chunk's toolResults array and add to accumulated data
                    foreach ($chunk->toolResults as $toolResult) {
                        $toolResultData = [
                            'result' => $toolResult->result ?? '',
                            'toolName' => $toolResult->toolName ?? 'unknown',
                            'toolCallId' => $toolResult->toolCallId ?? null,
                            'args' => $toolResult->args ?? [],
                            'toolCallResultId' => $toolResult->toolCallResultId ?? null,
                        ];
                        $streamData['toolResults'][] = $toolResultData;
                    }
                    break;
                default:
                    $streamData[$chunk->chunkType->value] .= $chunk->text;
            }

            $this->stream(
                'streamed-message',
                json_encode([...$streamData, 'currentChunkType' => $chunk->chunkType->value]),
                true
            );
        }

        // Create separate ToolResultMessage if we have tool results
        if ($streamData['toolResults'] !== []) {
            $this->chat->messages()->create([
                'role' => 'tool_result',
                'parts' => ['toolResults' => $streamData['toolResults']],
                'attachments' => '[]',
            ]);
        }

        // Create AssistantMessage without tool results in additionalContent
        if ($streamData !== []) {
            $this->chat->messages()->create([
                'role' => 'assistant',
                'parts' => $streamData,
                'attachments' => '[]',
            ]);
            $this->chat->touch();

            unset($this->messages);
        }
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
        return view('livewire.chats.show');
    }
}
