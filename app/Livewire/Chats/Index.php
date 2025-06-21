<?php

namespace App\Livewire\Chats;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

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

    public function render(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $chats = $user->chats()
            ->withCount('messages')
            ->latest('updated_at')
            ->paginate(10);

        return view('livewire.chats.index', [
            'chats' => $chats,
        ]);
    }
}
