<?php

use App\Models\User;
use Database\Factories\ConversationFactory;

use function Pest\Laravel\actingAs;

it('loads the guest pages without javascript errors', function (): void {
    visit([
        route('login'),
        route('register'),
        route('password.request'),
    ])->assertNoSmoke();
});

it('loads the app pages without javascript errors', function (): void {
    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->withMessages()->create();

    actingAs($user);

    visit([
        route('conversations.index'),
        route('conversation.create'),
        route('conversation.show', $conversation),
        route('settings'),
    ])->assertNoSmoke();
});
