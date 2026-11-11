<?php

namespace App\Livewire\Chats;

use App\Actions\UpdateStreamDataFromAiEvent;
use App\Ai\Agents\ChatAgent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Ai\Models\Conversation;
use Laravel\Ai\Models\ConversationMessage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Show extends Component
{
    #[Locked]
    public Conversation $conversation;

    public string $newMessage = '';

    #[Locked]
    public string $pendingMessage = '';

    public function mount(Conversation $conversation): void
    {
        abort_unless($conversation->participant?->is(Auth::user()), 403);

        $this->conversation = $conversation;
    }

    /**
     * @return array<int, array{id: string, role: string, content: string, toolCalls: array<int, array<string, mixed>>}>
     */
    #[Computed]
    public function messages(): array
    {
        return $this->conversation
            ->messages()
            ->orderBy('id')
            ->get()
            ->map(fn (ConversationMessage $message): array => [
                'id' => $message->id,
                'role' => $message->role,
                'content' => (string) $message->content,
                'toolCalls' => $message->tool_calls,
            ])
            ->all();
    }

    public function sendMessage(): void
    {
        $message = trim($this->newMessage);

        if ($message === '') {
            return;
        }

        $this->pendingMessage = $message;
        $this->newMessage = '';

        $this->js('$wire.runAgent()');
    }

    public function runAgent(UpdateStreamDataFromAiEvent $updateStreamData): void
    {
        if ($this->pendingMessage === '') {
            return;
        }

        if ($this->conversation->messages()->doesntExist()) {
            $this->conversation->update([
                'title' => Str::limit($this->pendingMessage, 50, preserveWords: true),
            ]);
        }

        $stream = ChatAgent::make()
            ->continue($this->conversation->id, as: Auth::user())
            ->stream($this->pendingMessage);

        $streamData = $updateStreamData->initial();

        foreach ($stream as $event) {
            $streamData = $updateStreamData->handle($streamData, $event);

            $this->stream(
                json_encode($streamData),
                true,
                to: 'streamed-message',
            );
        }

        $this->pendingMessage = '';

        unset($this->messages);

        $this->dispatch('conversation-updated');
    }

    public function render(): View
    {
        return view('livewire.chats.show');
    }
}
