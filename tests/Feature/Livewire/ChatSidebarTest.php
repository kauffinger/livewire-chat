<?php

use App\Models\AgentConversation;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('mounts successfully', function (): void {
    actingAs(User::factory()->create());

    livewire(\App\Livewire\ChatSidebar::class)
        ->assertOk()
        ->assertSeeText('Conversations')
        ->assertSeeText('New Conversation');
});

it('shows new conversation button for authenticated users', function (): void {
    actingAs(User::factory()->create());

    livewire(\App\Livewire\ChatSidebar::class)
        ->assertOk()
        ->assertSeeHtml('wire:click="createNewConversation"')
        ->assertDontSeeHtml('href="'.route('login').'"');
});

it('shows login link for guests', function (): void {
    livewire(\App\Livewire\ChatSidebar::class)
        ->assertOk()
        ->assertSeeHtml('href="'.route('login').'"')
        ->assertDontSeeHtml('wire:click="createNewConversation"');
});

it('displays user conversations', function (): void {
    $user = User::factory()->create();
    AgentConversation::factory()->recycle($user)->count(3)->create([
        'title' => 'My Chat',
    ]);

    actingAs($user);

    livewire(\App\Livewire\ChatSidebar::class)
        ->assertOk()
        ->assertSeeText('My Chat');
});

it('limits displayed conversations to 5 most recent', function (): void {
    $user = User::factory()->create();
    AgentConversation::factory()->recycle($user)->count(3)->create([
        'title' => 'Old Conversation',
        'updated_at' => now()->subDays(10),
    ]);
    AgentConversation::factory()->recycle($user)->count(5)->create([
        'title' => 'Recent Conversation',
        'updated_at' => now(),
    ]);

    actingAs($user);

    livewire(\App\Livewire\ChatSidebar::class)
        ->assertOk()
        ->assertSeeText('Recent Conversation')
        ->assertDontSeeText('Old Conversation');
});

it('highlights active conversation', function (): void {
    $user = User::factory()->create();
    $conversation = AgentConversation::factory()->recycle($user)->create();

    actingAs($user);

    livewire(\App\Livewire\ChatSidebar::class)
        ->set('activeConversationId', $conversation->id)
        ->assertOk()
        ->assertSet('activeConversationId', $conversation->id);
});

it('creates new conversation and redirects', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    livewire(\App\Livewire\ChatSidebar::class)
        ->call('createNewConversation')
        ->assertRedirect();

    expect($user->conversations)->toHaveCount(1)
        ->and($user->conversations()->first()->title)->toBe('New chat');
});

it('shows show all conversations button for authenticated users', function (): void {
    actingAs(User::factory()->create());

    livewire(\App\Livewire\ChatSidebar::class)
        ->assertOk()
        ->assertSeeText('Show All Conversations')
        ->assertSeeHtml('href="'.route('conversations.index').'"');
});

it('does not show show all conversations button for guests', function (): void {
    livewire(\App\Livewire\ChatSidebar::class)
        ->assertOk()
        ->assertDontSeeText('Show All Conversations');
});

it('truncates long conversation titles', function (): void {
    $user = User::factory()->create();
    $longTitle = 'This is a very long conversation title that should be truncated';
    AgentConversation::factory()->recycle($user)->create(['title' => $longTitle]);

    actingAs($user);

    livewire(\App\Livewire\ChatSidebar::class)
        ->assertOk()
        ->assertSeeText('This is a very long...')
        ->assertDontSeeText($longTitle);
});
