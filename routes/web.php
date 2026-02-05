<?php

use App\Livewire\Chats\Index as ConversationsIndex;
use App\Livewire\Chats\Show as ConversationsShow;
use App\Livewire\Dashboard as DashboardComponent;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'dashboard')->name('home');

Route::get('dashboard', DashboardComponent::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function (): void {
    Route::get('conversations', ConversationsIndex::class)->name('conversations.index');
    Route::get('conversations/{conversation}', ConversationsShow::class)->name('conversation.show');

    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

require __DIR__.'/auth.php';
