<?php

namespace App\Livewire;

use App\Models\AgentConversation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ChatSidebar extends Component
{
    public ?string $activeConversationId = null;

    public function mount(): void
    {
        $this->activeConversationId = request()->route('conversation')?->id;
    }

    #[Computed]
    public function conversations(): array
    {
        return Auth::user()
            ?->conversations()
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (AgentConversation $conversation) => [
                'id' => $conversation->id,
                'title' => (string) Str::limit($conversation->title, 20),
            ])
            ->all() ?? [];
    }

    public function createNewConversation(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $conversation = AgentConversation::create([
            'id' => (string) Str::uuid7(),
            'user_id' => $user->id,
            'title' => __('New chat'),
        ]);

        $this->redirect(route('conversation.show', $conversation), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.chat-sidebar');
    }
}
