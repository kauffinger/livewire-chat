<?php

use App\Livewire\ChatSidebar;
use App\Models\User;
use Database\Factories\ConversationFactory;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
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

it('limits displayed conversations to the 30 most recent', function (): void {
    $user = User::factory()->create();
    ConversationFactory::new()->recycle($user)->count(3)->create([
        'title' => 'Old Conversation',
        'updated_at' => now()->subDays(10),
    ]);
    ConversationFactory::new()->recycle($user)->count(30)->create([
        'title' => 'Recent Conversation',
        'updated_at' => now(),
    ]);

    actingAs($user);

    livewire(ChatSidebar::class)
        ->assertOk()
        ->assertSeeText('Recent Conversation')
        ->assertDontSeeText('Old Conversation');
});

it('marks only the open conversation as current', function (): void {
    $user = User::factory()->create();
    [$open, $other] = ConversationFactory::new()->recycle($user)->count(2)->create();

    actingAs($user);

    $page = get(route('conversation.show', $open))->assertOk()->getContent();

    expect($page)
        ->toMatch('/<a[^>]*href="'.preg_quote(route('conversation.show', $open), '/').'"[^>]*data-current="/')
        ->not->toMatch('/<a[^>]*href="'.preg_quote(route('conversation.show', $other), '/').'"[^>]*data-current="/');
});

it('groups conversations by when they were last active', function (): void {
    $user = User::factory()->create();

    foreach (['Fresh' => now(), 'A day old' => now()->subDay(), 'A few days old' => now()->subDays(4), 'Ancient' => now()->subDays(40)] as $title => $updatedAt) {
        ConversationFactory::new()->recycle($user)->create(['title' => $title, 'updated_at' => $updatedAt]);
    }

    actingAs($user);

    livewire(ChatSidebar::class)->assertSeeTextInOrder([
        'Today', 'Fresh',
        'Yesterday', 'A day old',
        'Previous 7 days', 'A few days old',
        'Older', 'Ancient',
    ]);
});

it('links authenticated users to the conversation search', function (): void {
    actingAs(User::factory()->create());

    livewire(ChatSidebar::class)
        ->assertOk()
        ->assertSeeText('Search conversations')
        ->assertSeeHtml('href="'.route('conversations.index').'"');
});

it('does not link guests to the conversation search', function (): void {
    livewire(ChatSidebar::class)
        ->assertOk()
        ->assertDontSeeText('Search conversations');
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

it('shows the new name once the profile is updated', function (): void {
    $user = User::factory()->create(['name' => 'Grace Hopper']);

    actingAs($user);

    $component = livewire(ChatSidebar::class)->assertSeeText('Grace Hopper');

    $user->update(['name' => 'Ada Lovelace']);

    $component
        ->dispatch('profile-updated')
        ->assertSeeText('Ada Lovelace')
        ->assertDontSeeText('Grace Hopper');
});
