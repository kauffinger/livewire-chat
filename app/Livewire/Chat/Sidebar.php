<?php

namespace App\Livewire\Chat;

use App\Models\Chat as ChatModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class Sidebar extends Component
{
    /**
     * The list of chats for the current user.
     *
     * @var array<int, array{ id: string, title: string }>
     */
    public array $chats = [];

    public ?string $activeChatId = null;

    public function mount(): void
    {
        $this->activeChatId = request()->route('chat')?->id;
        $this->refreshChats();
    }

    public function refreshChats(): void
    {
        $this->chats = Auth::user()
            ?->chats()
            ->latest()
            ->get()
            ->map(fn (ChatModel $chat) => [
                'id' => $chat->id,
                'title' => (string) Str::limit($chat->title, 20),
            ])
            ->all() ?? [];
    }

    public function createNewChat(): void
    {
        $chat = Auth::user()->chats()->create([
            'title' => __('New chat'),
            'model' => 'gpt-4o-mini',
        ]);

        $this->refreshChats();

        $this->redirect(route('chat.show', $chat), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.chat.sidebar');
    }
}
