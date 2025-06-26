<?php

use App\Models\Chat;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('mounts', function (): void {
    actingAs(User::factory()->create());

    livewire(\App\Livewire\Chats\Index::class)
        ->assertOk()
        ->assertSeeText('No chats yet');
});

it('shows chats', function (): void {
    $user = User::factory()->create();
    Chat::factory()->recycle($user)->count(20)->create();
    actingAs($user);

    livewire(\App\Livewire\Chats\Index::class)
        ->assertOk()
        ->assertSeeText($user->chats()->oldest()->first()->title)
        ->assertSeeHtml('wire:click="gotoPage(2, \'page\')"');
});

it('creates new chat and redirects', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    livewire(\App\Livewire\Chats\Index::class)
        ->call('createNewChat')
        ->assertRedirect(route('chat.show', $user->chats()->latest()->first()));

    expect($user->chats)->toHaveCount(1)
        ->and($user->chats()->first()->title)->toBe('New chat')
        ->and($user->chats()->first()->model)->toBe('gpt-4o-mini');
});

it('displays chat metadata', function (): void {
    $user = User::factory()->create();
    $chat = Chat::factory()->recycle($user)->create([
        'created_at' => now()->subDays(5),
        'updated_at' => now()->subHours(2),
    ]);

    // Add messages to get accurate count
    $chat->messages()->createMany([
        ['role' => 'user', 'parts' => ['text' => 'Hello'], 'attachments' => []],
        ['role' => 'assistant', 'parts' => ['text' => 'Hi there'], 'attachments' => []],
        ['role' => 'user', 'parts' => ['text' => 'How are you?'], 'attachments' => []],
    ]);

    actingAs($user);

    livewire(\App\Livewire\Chats\Index::class)
        ->assertOk()
        ->assertSeeText($chat->title)
        ->assertSeeText('5 days ago') // created_at
        ->assertSeeText('2 hours ago') // updated_at
        ->assertSeeText('3'); // messages_count
});

it('shows chats in descending order by updated_at', function (): void {
    $user = User::factory()->create();
    $oldChat = Chat::factory()->recycle($user)->create([
        'title' => 'Old Chat',
        'updated_at' => now()->subDays(10),
    ]);
    $recentChat = Chat::factory()->recycle($user)->create([
        'title' => 'Recent Chat',
        'updated_at' => now(),
    ]);

    actingAs($user);

    $response = livewire(\App\Livewire\Chats\Index::class);

    // Recent chat should appear before old chat
    $htmlContent = $response->html();
    $recentChatPosition = strpos($htmlContent, 'Recent Chat');
    $oldChatPosition = strpos($htmlContent, 'Old Chat');

    expect($recentChatPosition)->toBeLessThan($oldChatPosition);
});

it('paginates chats with 10 per page', function (): void {
    $user = User::factory()->create();
    Chat::factory()->recycle($user)->count(15)->create();
    actingAs($user);

    livewire(\App\Livewire\Chats\Index::class)
        ->assertOk()
        ->assertSeeHtml('wire:click="gotoPage(2, \'page\')"')
        ->assertDontSeeHtml('wire:click="gotoPage(3, \'page\')"');
});

it('shows empty state with create button when no chats', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    livewire(\App\Livewire\Chats\Index::class)
        ->assertOk()
        ->assertSeeText('No chats yet')
        ->assertSeeText('Start your first conversation by creating a new chat.')
        ->assertSeeText('Create Your First Chat')
        ->assertSeeHtml('wire:click="createNewChat"');
});

it('shows all chat action buttons', function (): void {
    $user = User::factory()->create();
    $chat = Chat::factory()->recycle($user)->create();
    actingAs($user);

    livewire(\App\Livewire\Chats\Index::class)
        ->assertOk()
        ->assertSeeText('Open')
        ->assertSeeHtml('href="'.route('chat.show', $chat->id).'"')
        ->assertSeeHtml('wire:navigate.hover');
});
