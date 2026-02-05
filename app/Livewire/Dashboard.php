<?php

namespace App\Livewire;

use App\Models\AgentConversation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class Dashboard extends Component
{
    /**
     * If the user has no conversations, we show the create button. Otherwise we redirect.
     */
    public bool $showCreate = false;

    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $latestConversation = $user->conversations()->latest()->first();

        if ($latestConversation) {
            $this->redirectRoute('conversation.show', ['conversation' => $latestConversation->id], navigate: true);

            return;
        }

        $this->showCreate = true;
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

        $this->redirectRoute('conversation.show', ['conversation' => $conversation->id], navigate: true);
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
