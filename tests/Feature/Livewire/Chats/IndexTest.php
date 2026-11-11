<?php

use App\Livewire\Chats\Index;
use App\Models\User;
use Database\Factories\ConversationFactory;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

it('mounts', function (): void {
    actingAs(User::factory()->create());

    livewire(Index::class)
        ->assertOk()
        ->assertSeeText('No conversations yet');
});

it('shows conversations', function (): void {
    $user = User::factory()->create();
    ConversationFactory::new()->recycle($user)->count(20)->create(['title' => 'Test Title']);
    actingAs($user);

    livewire(Index::class)
        ->assertOk()
        ->assertSeeText('Test Title')
        ->assertSeeHtml('wire:click="gotoPage(2, \'page\')"');
});

it('links to a new conversation', function (): void {
    $user = User::factory()->create();
    ConversationFactory::new()->recycle($user)->create();

    actingAs($user);

    livewire(Index::class)
        ->assertSeeHtml('href="'.route('conversation.create').'"');
});

it('displays conversation metadata', function (): void {
    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create([
        'created_at' => now()->subDays(5),
        'updated_at' => now()->subHours(2),
    ]);

    actingAs($user);

    livewire(Index::class)
        ->assertOk()
        ->assertSeeText($conversation->title)
        ->assertSeeText('5 days ago') // created_at
        ->assertSeeText('2 hours ago'); // updated_at
});

it('shows conversations in descending order by updated_at', function (): void {
    $user = User::factory()->create();
    ConversationFactory::new()->recycle($user)->create([
        'title' => 'Old Conversation',
        'updated_at' => now()->subDays(10),
    ]);
    ConversationFactory::new()->recycle($user)->create([
        'title' => 'Recent Conversation',
        'updated_at' => now(),
    ]);

    actingAs($user);

    $response = livewire(Index::class);

    // Recent conversation should appear before old conversation
    $htmlContent = $response->html();
    $recentPosition = strpos($htmlContent, 'Recent Conversation');
    $oldPosition = strpos($htmlContent, 'Old Conversation');

    expect($recentPosition)->toBeLessThan($oldPosition);
});

it('paginates conversations with 10 per page', function (): void {
    $user = User::factory()->create();
    ConversationFactory::new()->recycle($user)->count(15)->create();
    actingAs($user);

    livewire(Index::class)
        ->assertOk()
        ->assertSeeHtml('wire:click="gotoPage(2, \'page\')"')
        ->assertDontSeeHtml('wire:click="gotoPage(3, \'page\')"');
});

it('shows empty state with create button when no conversations', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    livewire(Index::class)
        ->assertOk()
        ->assertSeeText('No conversations yet')
        ->assertSeeText('Start your first conversation by creating a new one.')
        ->assertSeeText('Create Your First Conversation')
        ->assertSeeHtml('href="'.route('conversation.create').'"');
});

it('shows all conversation action buttons', function (): void {
    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create();
    actingAs($user);

    livewire(Index::class)
        ->assertOk()
        ->assertSeeText('Open')
        ->assertSeeHtml('href="'.route('conversation.show', $conversation->id).'"')
        ->assertSeeHtml('wire:navigate.hover');
});

it('renders the conversations page', function (): void {
    $user = User::factory()->create();
    ConversationFactory::new()->recycle($user)->create(['title' => 'Test Title']);

    actingAs($user);

    get(route('conversations.index'))
        ->assertOk()
        ->assertSeeLivewire(Index::class)
        ->assertSeeText('Test Title');
});

it('only lists the conversations of the current user', function (): void {
    $user = User::factory()->create();
    ConversationFactory::new()->recycle($user)->create(['title' => 'Mine']);
    ConversationFactory::new()->create(['title' => 'Somebody else\'s']);

    actingAs($user);

    livewire(Index::class)
        ->assertSeeText('Mine')
        ->assertDontSeeText('Somebody else\'s');
});
