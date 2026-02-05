<?php

namespace App\Livewire\Chats;

use App\Ai\Agents\ChatAgent;
use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Ai\Streaming\Events\ReasoningDelta;
use Laravel\Ai\Streaming\Events\TextDelta;
use Laravel\Ai\Streaming\Events\ToolCall;
use Laravel\Ai\Streaming\Events\ToolResult;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Show extends Component
{
    #[Locked]
    public AgentConversation $conversation;

    public string $newMessage = '';

    public function mount(AgentConversation $conversation): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless($user && $conversation->user_id === $user->id, 403);

        $this->conversation = $conversation;
    }

    #[Computed]
    public function messages(): array
    {
        return $this->conversation
            ->messages()
            ->oldest()
            ->get()
            ->map(fn (AgentConversationMessage $message) => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->all();
    }

    public function sendMessage(): void
    {
        if (trim($this->newMessage) === '') {
            return;
        }

        $this->js('$wire.runAgent()');
    }

    public function runAgent(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $text = '';
        $thinking = '';
        $toolCalls = [];
        $toolResults = [];

        $stream = ChatAgent::make()
            ->continue($this->conversation->id, as: $user)
            ->stream($this->newMessage);

        foreach ($stream as $event) {
            if ($event instanceof TextDelta) {
                $text .= $event->delta;
            } elseif ($event instanceof ReasoningDelta) {
                $thinking .= $event->delta;
            } elseif ($event instanceof ToolCall) {
                $toolCalls[] = [
                    'name' => $event->name,
                    'arguments' => $event->arguments,
                ];
            } elseif ($event instanceof ToolResult) {
                $toolResults[] = [
                    'name' => $event->name,
                    'result' => $event->result,
                ];
            }

            $this->stream(
                json_encode([
                    'text' => $text,
                    'thinking' => $thinking,
                    'toolCalls' => $toolCalls,
                    'toolResults' => $toolResults,
                    'currentChunkType' => $event->type(),
                ]),
                true,
                to: 'streamed-message',
            );
        }

        $this->newMessage = '';
        unset($this->messages);
    }

    public function render(): View
    {
        return view('livewire.chats.show');
    }
}
