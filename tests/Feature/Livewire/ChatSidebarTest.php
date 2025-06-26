<?php

use App\Models\Chat;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('mounts successfully', function (): void {
    actingAs(User::factory()->create());

    livewire(\App\Livewire\ChatSidebar::class)
        ->assertOk()
        ->assertSeeText('Chats')
        ->assertSeeText('New Chat');
});

it('shows new chat button for authenticated users', function (): void {
    actingAs(User::factory()->create());

    livewire(\App\Livewire\ChatSidebar::class)
        ->assertOk()
        ->assertSeeHtml('wire:click="createNewChat"')
        ->assertDontSeeHtml('href="'.route('login').'"');
});

it('shows login link for guests', function (): void {
    livewire(\App\Livewire\ChatSidebar::class)
        ->assertOk()
        ->assertSeeHtml('href="'.route('login').'"')
        ->assertDontSeeHtml('wire:click="createNewChat"');
});

it('displays user chats', function (): void {
    $user = User::factory()->create();
    $chats = Chat::factory()->recycle($user)->count(3)->create([
        'title' => 'Test Chat Title',
    ]);

    actingAs($user);

    livewire(\App\Livewire\ChatSidebar::class)
        ->assertOk()
        ->assertSeeText('Test Chat Title');
});

it('limits displayed chats to 5 most recent', function (): void {
    $user = User::factory()->create();
    Chat::factory()->recycle($user)->count(3)->create([
        'title' => 'Old Chat',
        'updated_at' => now()->subDays(10),
    ]);
    Chat::factory()->recycle($user)->count(5)->create([
        'title' => 'Recent Chat',
        'updated_at' => now(),
    ]);

    actingAs($user);

    livewire(\App\Livewire\ChatSidebar::class)
        ->assertOk()
        ->assertSeeText('Recent Chat')
        ->assertDontSeeText('Old Chat');
});

it('highlights active chat', function (): void {
    $user = User::factory()->create();
    $chat = Chat::factory()->recycle($user)->create();

    actingAs($user);

    livewire(\App\Livewire\ChatSidebar::class)
        ->set('activeChatId', $chat->id)
        ->assertOk()
        ->assertSet('activeChatId', $chat->id);
});

it('creates new chat and redirects', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    livewire(\App\Livewire\ChatSidebar::class)
        ->call('createNewChat')
        ->assertRedirect(route('chat.show', $user->chats()->latest()->first()));

    expect($user->chats)->toHaveCount(1)
        ->and($user->chats()->first()->title)->toBe('New chat')
        ->and($user->chats()->first()->model)->toBe('gpt-4o-mini');
});

it('shows show all chats button for authenticated users', function (): void {
    actingAs(User::factory()->create());

    livewire(\App\Livewire\ChatSidebar::class)
        ->assertOk()
        ->assertSeeText('Show All Chats')
        ->assertSeeHtml('href="'.route('chats.index').'"');
});

it('does not show show all chats button for guests', function (): void {
    livewire(\App\Livewire\ChatSidebar::class)
        ->assertOk()
        ->assertDontSeeText('Show All Chats');
});

it('truncates long chat titles', function (): void {
    $user = User::factory()->create();
    $longTitle = 'This is a very long chat title that should be truncated';
    $chat = Chat::factory()->recycle($user)->create(['title' => $longTitle]);

    actingAs($user);

    livewire(\App\Livewire\ChatSidebar::class)
        ->assertOk()
        ->assertSeeText('This is a very long...')
        ->assertDontSeeText($longTitle);
});
