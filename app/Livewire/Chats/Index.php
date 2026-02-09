<?php

namespace App\Livewire\Chats;

use App\Models\AgentConversation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function createNewConversation(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $conversation = AgentConversation::create([
            'id' => (string) Str::uuid7(),
            'user_id' => $user->id,
            'title' => __('New chat'),
        ]);

        $this->redirectRoute('conversation.show', ['conversation' => $conversation->id], navigate: true);
    }

    public function render(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $conversations = $user->conversations()
            ->latest('updated_at')
            ->paginate(10);

        return view('livewire.chats.index', [
            'conversations' => $conversations,
        ]);
    }
}
