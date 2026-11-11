<?php

use App\Ai\Agents\ChatAgent;
use App\Livewire\Chats\Index as ConversationsIndex;
use App\Livewire\Chats\Show as ConversationsShow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'dashboard')->name('home');

Route::get('dashboard', function (Request $request) {
    $conversationId = ChatAgent::make()->continueLastConversation($request->user())->currentConversation();

    return $conversationId
        ? to_route('conversation.show', $conversationId)
        : to_route('conversation.create');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function (): void {
    Route::get('conversations', ConversationsIndex::class)->name('conversations.index');
    Route::get('conversations/new', ConversationsShow::class)->name('conversation.create');
    Route::get('conversations/{conversation}', ConversationsShow::class)->name('conversation.show');

    Route::view('settings', 'settings')->name('settings');
});

require __DIR__.'/auth.php';
