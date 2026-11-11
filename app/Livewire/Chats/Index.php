<?php

namespace App\Livewire\Chats;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.chats.index', [
            'conversations' => Auth::user()
                ->conversations()
                ->when($this->search !== '', fn (Builder $query) => $query->whereLike('title', "%{$this->search}%"))
                ->latest('updated_at')
                ->paginate(10),
        ])->title(__('Conversations'));
    }
}
