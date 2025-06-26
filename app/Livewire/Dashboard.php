<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    /**
     * If the user has no chats, we show the create button. Otherwise we redirect.
     */
    public bool $showCreate = false;

    public function mount(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $latestChat = $user->chats()->latest()->first();

        if ($latestChat) {
            // Redirect to the latest chat.
            $this->redirectRoute('chat.show', ['chat' => $latestChat->id], navigate: true);

            return;
        }

        // No chats yet → display create button.
        $this->showCreate = true;
    }

    public function createNewChat(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $chat = $user->chats()->create([
            'title' => __('New chat'),
            'model' => 'gpt-4o-mini',
        ]);

        $this->redirectRoute('chat.show', ['chat' => $chat->id], navigate: true);
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
