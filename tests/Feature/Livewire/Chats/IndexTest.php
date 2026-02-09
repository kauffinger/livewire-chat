<?php

use App\Models\AgentConversation;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('mounts', function (): void {
    actingAs(User::factory()->create());

    livewire(\App\Livewire\Chats\Index::class)
        ->assertOk()
        ->assertSeeText('No conversations yet');
});

it('shows conversations', function (): void {
    $user = User::factory()->create();
    AgentConversation::factory()->recycle($user)->count(20)->create(['title' => 'Test Title']);
    actingAs($user);

    livewire(\App\Livewire\Chats\Index::class)
        ->assertOk()
        ->assertSeeText('Test Title')
        ->assertSeeHtml('wire:click="gotoPage(2, \'page\')"');
});

it('creates new conversation and redirects', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    livewire(\App\Livewire\Chats\Index::class)
        ->call('createNewConversation')
        ->assertRedirect();

    expect($user->conversations)->toHaveCount(1)
        ->and($user->conversations()->first()->title)->toBe('New chat');
});

it('displays conversation metadata', function (): void {
    $user = User::factory()->create();
    $conversation = AgentConversation::factory()->recycle($user)->create([
        'created_at' => now()->subDays(5),
        'updated_at' => now()->subHours(2),
    ]);

    actingAs($user);

    livewire(\App\Livewire\Chats\Index::class)
        ->assertOk()
        ->assertSeeText($conversation->title)
        ->assertSeeText('5 days ago') // created_at
        ->assertSeeText('2 hours ago'); // updated_at
});

it('shows conversations in descending order by updated_at', function (): void {
    $user = User::factory()->create();
    AgentConversation::factory()->recycle($user)->create([
        'title' => 'Old Conversation',
        'updated_at' => now()->subDays(10),
    ]);
    AgentConversation::factory()->recycle($user)->create([
        'title' => 'Recent Conversation',
        'updated_at' => now(),
    ]);

    actingAs($user);

    $response = livewire(\App\Livewire\Chats\Index::class);

    // Recent conversation should appear before old conversation
    $htmlContent = $response->html();
    $recentPosition = strpos($htmlContent, 'Recent Conversation');
    $oldPosition = strpos($htmlContent, 'Old Conversation');

    expect($recentPosition)->toBeLessThan($oldPosition);
});

it('paginates conversations with 10 per page', function (): void {
    $user = User::factory()->create();
    AgentConversation::factory()->recycle($user)->count(15)->create();
    actingAs($user);

    livewire(\App\Livewire\Chats\Index::class)
        ->assertOk()
        ->assertSeeHtml('wire:click="gotoPage(2, \'page\')"')
        ->assertDontSeeHtml('wire:click="gotoPage(3, \'page\')"');
});

it('shows empty state with create button when no conversations', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    livewire(\App\Livewire\Chats\Index::class)
        ->assertOk()
        ->assertSeeText('No conversations yet')
        ->assertSeeText('Start your first conversation by creating a new one.')
        ->assertSeeText('Create Your First Conversation')
        ->assertSeeHtml('wire:click="createNewConversation"');
});

it('shows all conversation action buttons', function (): void {
    $user = User::factory()->create();
    $conversation = AgentConversation::factory()->recycle($user)->create();
    actingAs($user);

    livewire(\App\Livewire\Chats\Index::class)
        ->assertOk()
        ->assertSeeText('Open')
        ->assertSeeHtml('href="'.route('conversation.show', $conversation->id).'"')
        ->assertSeeHtml('wire:navigate.hover');
});
