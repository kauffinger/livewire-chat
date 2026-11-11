<?php

use App\Models\User;
use Database\Factories\ConversationFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function (): void {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('users without conversations are sent to a new conversation', function (): void {
    actingAs(User::factory()->create());

    get('/dashboard')->assertRedirect(route('conversation.create'));
});

test('users with conversations are redirected to their latest conversation', function (): void {
    $user = User::factory()->create();
    ConversationFactory::new()->recycle($user)->withMessages()->create();
    $latestConversation = ConversationFactory::new()->recycle($user)->withMessages()->create();

    actingAs($user);

    get('/dashboard')->assertRedirect(route('conversation.show', $latestConversation));
});

test('users are not redirected to the conversations of other users', function (): void {
    ConversationFactory::new()->withMessages()->create();

    actingAs(User::factory()->create());

    get('/dashboard')->assertRedirect(route('conversation.create'));
});
