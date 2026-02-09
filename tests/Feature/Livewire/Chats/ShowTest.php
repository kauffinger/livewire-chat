<?php

use App\Ai\Agents\ChatAgent;
use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use App\Models\User;
use Illuminate\Support\Str;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('shows user\'s conversation', function (): void {
    $user = User::factory()->create();
    $conversation = AgentConversation::factory()
        ->recycle($user)
        ->withMessages()
        ->create();

    actingAs($user);

    livewire(\App\Livewire\Chats\Show::class, ['conversation' => $conversation])
        ->assertOk();
});

it('disallows other users to show the conversation', function (): void {
    $user = User::factory()->create();
    $conversation = AgentConversation::factory()
        ->recycle($user)
        ->withMessages()
        ->create();

    $otherUser = User::factory()->create();
    actingAs($otherUser);

    livewire(\App\Livewire\Chats\Show::class, ['conversation' => $conversation])
        ->assertForbidden();
});

it('disallows guests to show the conversation', function (): void {
    $user = User::factory()->create();
    $conversation = AgentConversation::factory()
        ->recycle($user)
        ->withMessages()
        ->create();

    livewire(\App\Livewire\Chats\Show::class, ['conversation' => $conversation])
        ->assertForbidden();
});

it('contains the messages', function (): void {
    $user = User::factory()->create();
    $conversation = AgentConversation::factory()
        ->recycle($user)
        ->withMessages()
        ->create();

    actingAs($user);

    $firstMessage = $conversation->messages()->oldest()->first();

    livewire(\App\Livewire\Chats\Show::class, ['conversation' => $conversation])
        ->assertOk()
        ->assertSeeText($firstMessage->content);
});

it('allows sending messages', function (): void {
    ChatAgent::fake();

    $user = User::factory()->create();
    $conversation = AgentConversation::factory()->recycle($user)->create();

    actingAs($user);

    // newMessage stays until runAgent completes (for optimistic UI display)
    livewire(\App\Livewire\Chats\Show::class, ['conversation' => $conversation])
        ->set('newMessage', 'Hello, world!')
        ->call('sendMessage')
        ->assertSet('newMessage', 'Hello, world!')
        ->call('runAgent')
        ->assertSet('newMessage', '');
});

it('does not send empty messages', function (): void {
    $user = User::factory()->create();
    $conversation = AgentConversation::factory()->recycle($user)->create();

    actingAs($user);

    livewire(\App\Livewire\Chats\Show::class, ['conversation' => $conversation])
        ->set('newMessage', '')
        ->call('sendMessage')
        ->assertSet('newMessage', '');
});

it('trims whitespace from messages', function (): void {
    $user = User::factory()->create();
    $conversation = AgentConversation::factory()->recycle($user)->create();

    actingAs($user);

    livewire(\App\Livewire\Chats\Show::class, ['conversation' => $conversation])
        ->set('newMessage', '   ')
        ->call('sendMessage')
        ->assertSet('newMessage', '   ');
});

it('loads messages in chronological order', function (): void {
    $user = User::factory()->create();
    $conversation = AgentConversation::factory()->recycle($user)->create();

    AgentConversationMessage::create([
        'id' => (string) Str::uuid7(),
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'agent' => 'App\\Ai\\Agents\\ChatAgent',
        'role' => 'user',
        'content' => 'First message',
        'attachments' => [],
        'tool_calls' => [],
        'tool_results' => [],
        'usage' => [],
        'meta' => [],
        'created_at' => now()->subHours(3),
        'updated_at' => now()->subHours(3),
    ]);

    AgentConversationMessage::create([
        'id' => (string) Str::uuid7(),
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'agent' => 'App\\Ai\\Agents\\ChatAgent',
        'role' => 'assistant',
        'content' => 'Second message',
        'attachments' => [],
        'tool_calls' => [],
        'tool_results' => [],
        'usage' => [],
        'meta' => [],
        'created_at' => now()->subHours(2),
        'updated_at' => now()->subHours(2),
    ]);

    AgentConversationMessage::create([
        'id' => (string) Str::uuid7(),
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'agent' => 'App\\Ai\\Agents\\ChatAgent',
        'role' => 'user',
        'content' => 'Third message',
        'attachments' => [],
        'tool_calls' => [],
        'tool_results' => [],
        'usage' => [],
        'meta' => [],
        'created_at' => now()->subHour(),
        'updated_at' => now()->subHour(),
    ]);

    actingAs($user);

    $component = livewire(\App\Livewire\Chats\Show::class, ['conversation' => $conversation]);
    $messages = $component->get('messages');

    expect($messages)->toHaveCount(3)
        ->and($messages[0]['content'])->toBe('First message')
        ->and($messages[1]['content'])->toBe('Second message')
        ->and($messages[2]['content'])->toBe('Third message');
});
