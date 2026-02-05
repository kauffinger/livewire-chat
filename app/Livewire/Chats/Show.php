<?php

namespace App\Livewire\Chats;

use App\Actions\UpdateStreamDataFromAiEvent;
use App\Ai\Agents\ChatAgent;
use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
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

        $streamDataUpdater = app(UpdateStreamDataFromAiEvent::class);
        $streamData = $streamDataUpdater->initial();

        $stream = ChatAgent::make()
            ->continue($this->conversation->id, as: $user)
            ->stream($this->newMessage);

        foreach ($stream as $event) {
            $streamData = $streamDataUpdater->handle($streamData, $event);

            $this->stream(
                json_encode($streamData),
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
