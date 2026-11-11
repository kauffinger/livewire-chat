<?php

use App\Livewire\ChatSidebar;
use App\Models\User;
use Database\Factories\ConversationFactory;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('mounts successfully', function (): void {
    actingAs(User::factory()->create());

    livewire(ChatSidebar::class)
        ->assertOk()
        ->assertSeeText('New conversation');
});

it('links to a new conversation', function (): void {
    actingAs(User::factory()->create());

    livewire(ChatSidebar::class)
        ->assertSeeHtml('href="'.route('conversation.create').'"');
});

it('displays user conversations', function (): void {
    $user = User::factory()->create();
    ConversationFactory::new()->recycle($user)->count(3)->create([
        'title' => 'My Chat',
    ]);

    actingAs($user);

    livewire(ChatSidebar::class)
        ->assertOk()
        ->assertSeeText('My Chat');
});

it('limits displayed conversations to 5 most recent', function (): void {
    $user = User::factory()->create();
    ConversationFactory::new()->recycle($user)->count(3)->create([
        'title' => 'Old Conversation',
        'updated_at' => now()->subDays(10),
    ]);
    ConversationFactory::new()->recycle($user)->count(5)->create([
        'title' => 'Recent Conversation',
        'updated_at' => now(),
    ]);

    actingAs($user);

    livewire(ChatSidebar::class)
        ->assertOk()
        ->assertSeeText('Recent Conversation')
        ->assertDontSeeText('Old Conversation');
});

it('highlights active conversation', function (): void {
    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create();

    actingAs($user);

    livewire(ChatSidebar::class)
        ->set('activeConversationId', $conversation->id)
        ->assertOk()
        ->assertSet('activeConversationId', $conversation->id);
});

it('links authenticated users to all conversations', function (): void {
    actingAs(User::factory()->create());

    livewire(ChatSidebar::class)
        ->assertOk()
        ->assertSeeText('All conversations')
        ->assertSeeHtml('href="'.route('conversations.index').'"');
});

it('does not link guests to all conversations', function (): void {
    livewire(ChatSidebar::class)
        ->assertOk()
        ->assertDontSeeText('All conversations');
});

it('keeps long conversation titles whole for the browser to truncate', function (): void {
    $user = User::factory()->create();
    $longTitle = 'This is a very long conversation title that should be truncated';
    ConversationFactory::new()->recycle($user)->create(['title' => $longTitle]);

    actingAs($user);

    livewire(ChatSidebar::class)
        ->assertOk()
        ->assertSeeText($longTitle);
});

it('refreshes its conversations when a conversation is updated', function (): void {
    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create(['title' => 'New chat']);

    actingAs($user);

    $component = livewire(ChatSidebar::class)
        ->assertSeeText('New chat');

    $conversation->update(['title' => 'Unladen swallows']);

    $component
        ->dispatch('conversation-updated')
        ->assertSeeText('Unladen swallows')
        ->assertDontSeeText('New chat');
});

it('renders a unique key for every conversation', function (): void {
    $user = User::factory()->create();
    $conversations = ConversationFactory::new()->recycle($user)->count(2)->create();

    actingAs($user);

    livewire(ChatSidebar::class)
        ->assertSeeHtml('wire:key="conversation-'.$conversations[0]->id.'"')
        ->assertSeeHtml('wire:key="conversation-'.$conversations[1]->id.'"');
});
