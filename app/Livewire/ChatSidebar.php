<?php

namespace App\Livewire;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Ai\Models\Conversation;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ChatSidebar extends Component
{
    /**
     * @return Collection<string, Collection<int, Conversation>>
     */
    #[Computed]
    public function conversationGroups(): Collection
    {
        $conversations = Auth::user()
            ?->conversations()
            ->latest('updated_at')
            ->limit(30)
            ->get();

        return collect($conversations)->groupBy(fn (Conversation $conversation): string => match (true) {
            $conversation->updated_at->isToday() => __('Today'),
            $conversation->updated_at->isYesterday() => __('Yesterday'),
            $conversation->updated_at->gte(today()->subDays(7)) => __('Previous 7 days'),
            default => __('Older'),
        });
    }

    #[On('conversation-updated')]
    #[On('profile-updated')]
    public function refresh(): void
    {
        unset($this->conversationGroups);
    }

    public function render(): View
    {
        return view('livewire.chat-sidebar');
    }
}
