<?php

namespace App\Livewire\Chats;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function render(): View
    {
        return view('livewire.chats.index', [
            'conversations' => Auth::user()
                ->conversations()
                ->latest('updated_at')
                ->paginate(10),
        ])->title(__('Conversations'));
    }
}
