<?php

use App\Livewire\Dashboard;
use App\Models\User;
use Database\Factories\ConversationFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function (): void {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function (): void {
    $this->actingAs($user = User::factory()->create());

    $this->get('/dashboard')->assertStatus(200);
});

test('users with conversations are redirected to their latest conversation', function (): void {
    $user = User::factory()->create();
    ConversationFactory::new()->recycle($user)->create(['created_at' => now()->subDay()]);
    $latestConversation = ConversationFactory::new()->recycle($user)->create();

    actingAs($user);

    livewire(Dashboard::class)
        ->assertRedirect(route('conversation.show', $latestConversation));
});

test('users can start their first conversation from the dashboard', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    livewire(Dashboard::class)
        ->assertSet('showCreate', true)
        ->call('createNewConversation')
        ->assertRedirect(route('conversation.show', $user->conversations()->sole()));

    expect($user->conversations()->sole()->title)->toBe('New chat');
});
