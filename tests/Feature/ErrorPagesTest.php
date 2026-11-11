<?php

use App\Models\User;
use Database\Factories\ConversationFactory;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('shows its own page when a page does not exist', function (): void {
    get('/nowhere')
        ->assertNotFound()
        ->assertSeeText('Page not found')
        ->assertSeeHtml('href="'.route('home').'"');
});

it('shows its own page when a conversation belongs to someone else', function (): void {
    $conversation = ConversationFactory::new()->create();

    actingAs(User::factory()->create());

    get(route('conversation.show', $conversation))
        ->assertForbidden()
        ->assertSeeText('No access');
});
