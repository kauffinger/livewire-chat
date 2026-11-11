<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Ai\Models\Conversation;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ChatSidebar extends Component
{
    public ?string $activeConversationId = null;

    public function mount(): void
    {
        $this->activeConversationId = request()->route('conversation')?->id;
    }

    /**
     * @return array<int, array{id: string, title: string}>
     */
    #[Computed]
    public function conversations(): array
    {
        return Auth::user()
            ?->conversations()
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (Conversation $conversation): array => [
                'id' => $conversation->id,
                'title' => $conversation->title,
            ])
            ->all() ?? [];
    }

    #[On('conversation-updated')]
    public function refreshConversations(): void
    {
        unset($this->conversations);
    }

    public function render(): View
    {
        return view('livewire.chat-sidebar');
    }
}
