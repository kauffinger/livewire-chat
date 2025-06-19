<?php

namespace App\Livewire\Chat;

use App\Models\Chat as ChatModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Sidebar extends Component
{
    public ?string $activeChatId = null;

    public function mount(): void
    {
        $this->activeChatId = request()->route('chat')?->id;
    }

    #[Computed]
    public function chats(): array
    {
        return Auth::user()
            ?->chats()
            ->latest('updated_at')
            ->limit(10)
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

        $this->redirect(route('chat.show', $chat), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.chat.sidebar');
    }
}
