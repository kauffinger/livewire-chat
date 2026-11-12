<?php

namespace App\Livewire\Chats;

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

    #[Locked]
    public ?string $title = null;

    public string $newMessage = '';

    public string $pendingMessage = '';

    #[Locked]
    public int $messagesPerPage = 20;

    public function mount(?Conversation $conversation = null): void
    {
        abort_unless($conversation === null || $conversation->participant?->is(Auth::user()), 403);

        $this->conversationId = $conversation?->id;
        $this->title = $conversation?->title;
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
     * @return list<array{id: string, role: string, content: string, reasoning: string, toolCalls: list<array<string, mixed>>, failed: bool}>
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
                'reasoning' => collect($message->steps)->pluck('reasoning')->filter()->implode("\n\n"),
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

    public function runAgent(): void
    {
        $message = trim($this->pendingMessage);

        if ($message === '' || $this->pendingApprovals !== []) {
            $this->pendingMessage = '';

            return;
        }

        $this->streamAgent($message);
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
        return view('livewire.chats.show')->title($this->title ?? __('New conversation'));
    }

    protected function streamAgent(Decisions|string $prompt): void
    {
        $stream = ChatAgent::make()
            ->continueOrStart($this->conversationId, as: Auth::user())
            ->stream($prompt);

        try {
            foreach ($stream as $event) {
                $this->stream(to: 'reply', content: $event->toArray());
            }
        } catch (Throwable $exception) {
            report($exception);

            $this->newMessage = $this->pull('pendingMessage');

            Flux::toast(
                variant: 'danger',
                heading: __('The assistant could not respond.'),
                text: __('Please try again.'),
            );

            return;
        } finally {
            unset($this->history, $this->messages, $this->pendingApprovals);
            $this->renderIsland('messages');
        }

        // A new conversation keeps its pending message on screen until its own page takes over.
        if ($this->conversationId === null) {
            $this->redirectRoute('conversation.show', $stream->conversationId, navigate: true);

            return;
        }

        $this->pendingMessage = '';

        $this->dispatch('conversation-updated');
    }
}
