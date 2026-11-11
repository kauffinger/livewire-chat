<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    /**
     * If the user has no conversations, we show the create button. Otherwise we redirect.
     */
    public bool $showCreate = false;

    public function mount(): void
    {
        $latestConversation = Auth::user()->conversations()->latest()->first();

        if ($latestConversation) {
            $this->redirectRoute('conversation.show', $latestConversation, navigate: true);

            return;
        }

        $this->showCreate = true;
    }

    public function createNewConversation(): void
    {
        $this->redirectRoute('conversation.show', Auth::user()->startConversation(), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.dashboard');
    }
}
