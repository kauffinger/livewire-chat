<?php

namespace App\Livewire\Chats;

use App\Actions\UpdateStreamDataFromAiEvent;
use App\Ai\Agents\ChatAgent;
use Flux\Flux;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Ai\Approvals\Decision;
use Laravel\Ai\Approvals\Decisions;
use Laravel\Ai\Approvals\PendingApproval;
use Laravel\Ai\Contracts\ConversationStore;
use Laravel\Ai\Enums\MessageStatus;
use Laravel\Ai\Models\Conversation;
use Laravel\Ai\Storage\StoredMessage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

class Show extends Component
{
    #[Locked]
    public ?string $conversationId = null;

    public string $newMessage = '';

    #[Locked]
    public string $pendingMessage = '';

    #[Locked]
    public int $messagesPerPage = 20;

    #[Locked]
    public int $streamedTurns = 0;

    public function mount(?Conversation $conversation = null): void
    {
        abort_unless($conversation === null || $conversation->participant?->is(Auth::user()), 403);

        $this->conversationId = $conversation?->id;
    }

    /**
     * @return CursorPaginator<int, StoredMessage>|null
     */
    #[Computed]
    public function history(): ?CursorPaginator
    {
        if ($this->conversationId === null) {
            return null;
        }

        return resolve(ConversationStore::class)
            ->paginateConversationMessages($this->conversationId, $this->messagesPerPage);
    }

    /**
     * @return list<array{id: string, role: string, content: string, thinking: string, toolCalls: list<array<string, mixed>>, failed: bool}>
     */
    #[Computed]
    public function messages(): array
    {
        return collect($this->history?->items())
            ->reverse()
            ->map(fn (StoredMessage $message): array => [
                'id' => $message->id,
                'role' => $message->role,
                'content' => $message->content,
                'thinking' => collect($message->steps)->pluck('reasoning')->filter()->implode("\n\n"),
                'toolCalls' => $message->toolCalls(),
                'failed' => $message->status === MessageStatus::Failed,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<PendingApproval>
     */
    #[Computed]
    public function pendingApprovals(): array
    {
        if ($this->conversationId === null) {
            return [];
        }

        return resolve(ConversationStore::class)->pendingApprovalsFor($this->conversationId);
    }

    public function showEarlierMessages(): void
    {
        $this->messagesPerPage += 20;
    }

    public function sendMessage(): void
    {
        $message = trim($this->newMessage);

        if ($message === '' || $this->pendingApprovals !== []) {
            return;
        }

        $this->pendingMessage = $message;
        $this->newMessage = '';

        $this->js('$wire.runAgent()');
    }

    public function runAgent(): void
    {
        if ($this->pendingMessage === '') {
            return;
        }

        $this->streamAgent($this->pendingMessage);
    }

    public function approveToolCalls(): void
    {
        if ($this->pendingApprovals === []) {
            return;
        }

        $this->streamAgent(Decision::approveAll());
    }

    public function rejectToolCalls(): void
    {
        if ($this->pendingApprovals === []) {
            return;
        }

        $this->streamAgent(Decision::rejectAll(__('The user declined this action.')));
    }

    public function render(): View
    {
        return view('livewire.chats.show');
    }

    protected function streamAgent(Decisions|string $prompt): void
    {
        $stream = ChatAgent::make()
            ->continueOrStart($this->conversationId, as: Auth::user())
            ->stream($prompt);

        $updateStreamData = new UpdateStreamDataFromAiEvent;
        $streamData = $updateStreamData->initial();

        try {
            foreach ($stream as $event) {
                $streamData = $updateStreamData->handle($streamData, $event);

                // wire:stream writes its content to the page as HTML, so the tags in the reply must stay escaped.
                $this->stream(
                    json_encode($streamData, JSON_HEX_TAG | JSON_HEX_AMP),
                    true,
                    to: 'streamed-message',
                );
            }
        } catch (Throwable $exception) {
            report($exception);

            $this->newMessage = $this->pendingMessage;

            Flux::toast(
                variant: 'danger',
                heading: __('The assistant could not respond.'),
                text: __('Please try again.'),
            );

            return;
        } finally {
            $this->pendingMessage = '';
            $this->streamedTurns++;

            unset($this->history, $this->messages, $this->pendingApprovals);
            $this->renderIsland('messages');
        }

        if ($this->conversationId === null) {
            $this->redirectRoute('conversation.show', $stream->conversationId, navigate: true);

            return;
        }

        $this->dispatch('conversation-updated');
    }
}
