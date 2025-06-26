<?php

use App\Models\Chat;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('shows user\'s chat', function (): void {
    $user = User::factory()->create();
    $chat = Chat::factory()
        ->recycle($user)
        ->withMessages()
        ->create(['visibility' => 'private']);

    actingAs($user);

    livewire(\App\Livewire\Chats\Show::class, ['chat' => $chat])
        ->assertOk();
});

it('disallows others to show the chat', function (): void {
    $user = User::factory()->create();
    $chat = Chat::factory()
        ->recycle($user)
        ->withMessages()
        ->create(['visibility' => 'private']);

    $otherUser = User::factory()->create();
    actingAs($otherUser);

    livewire(\App\Livewire\Chats\Show::class, ['chat' => $chat])
        ->assertForbidden();
});

it('shows chat to other users if it\'s public', function (): void {
    $user = User::factory()->create();
    $chat = Chat::factory()
        ->recycle($user)
        ->withMessages()
        ->create(['visibility' => 'public']);

    $otherUser = User::factory()->create();
    actingAs($otherUser);

    livewire(\App\Livewire\Chats\Show::class, ['chat' => $chat])
        ->assertOk();
});

it('contains the messages', function (): void {
    $user = User::factory()->create();
    $chat = Chat::factory()
        ->recycle($user)
        ->withMessages()
        ->create(['visibility' => 'private']);

    actingAs($user);

    livewire(\App\Livewire\Chats\Show::class, ['chat' => $chat])
        ->assertOk()
        ->assertSeeText($chat->messages->first()->parts['text']);
});

it('allows sending messages', function (): void {
    $user = User::factory()->create();
    $chat = Chat::factory()->recycle($user)->create();

    actingAs($user);

    livewire(\App\Livewire\Chats\Show::class, ['chat' => $chat])
        ->set('newMessage', 'Hello, world!')
        ->call('sendMessage')
        ->assertSet('newMessage', '');

    expect($chat->fresh()->messages)->toHaveCount(1)
        ->and($chat->fresh()->messages->first()->role)->toBe('user')
        ->and($chat->fresh()->messages->first()->parts['text'])->toBe('Hello, world!');
});

it('does not send empty messages', function (): void {
    $user = User::factory()->create();
    $chat = Chat::factory()->recycle($user)->create();

    actingAs($user);

    livewire(\App\Livewire\Chats\Show::class, ['chat' => $chat])
        ->set('newMessage', '')
        ->call('sendMessage')
        ->assertSet('newMessage', '');

    expect($chat->fresh()->messages)->toHaveCount(0);
});

it('trims whitespace from messages', function (): void {
    $user = User::factory()->create();
    $chat = Chat::factory()->recycle($user)->create();

    actingAs($user);

    livewire(\App\Livewire\Chats\Show::class, ['chat' => $chat])
        ->set('newMessage', '   ')
        ->call('sendMessage')
        ->assertSet('newMessage', '   ');

    expect($chat->fresh()->messages)->toHaveCount(0);
});

it('allows changing chat visibility to public', function (): void {
    $user = User::factory()->create();
    $chat = Chat::factory()->recycle($user)->create(['visibility' => 'private']);

    actingAs($user);

    livewire(\App\Livewire\Chats\Show::class, ['chat' => $chat])
        ->call('share');

    expect($chat->fresh()->visibility)->toBe('public');
});

it('allows changing chat visibility to private', function (): void {
    $user = User::factory()->create();
    $chat = Chat::factory()->recycle($user)->create(['visibility' => 'public']);

    actingAs($user);

    livewire(\App\Livewire\Chats\Show::class, ['chat' => $chat])
        ->call('unshare');

    expect($chat->fresh()->visibility)->toBe('private');
});

it('allows changing the model', function (): void {
    $user = User::factory()->create();
    $chat = Chat::factory()->recycle($user)->create(['model' => 'gpt-4o-mini']);

    actingAs($user);

    livewire(\App\Livewire\Chats\Show::class, ['chat' => $chat])
        ->call('setModel', 'gpt-4o')
        ->assertSet('model', 'gpt-4o');

    expect($chat->fresh()->model)->toBe('gpt-4o');
});

it('loads messages in chronological order', function (): void {
    $user = User::factory()->create();
    $chat = Chat::factory()->recycle($user)->create();

    // Create messages with specific timestamps
    $chat->messages()->create([
        'role' => 'user',
        'parts' => ['text' => 'First message'],
        'attachments' => [],
        'created_at' => now()->subHours(3),
    ]);
    $chat->messages()->create([
        'role' => 'assistant',
        'parts' => ['text' => 'Second message'],
        'attachments' => [],
        'created_at' => now()->subHours(2),
    ]);
    $chat->messages()->create([
        'role' => 'user',
        'parts' => ['text' => 'Third message'],
        'attachments' => [],
        'created_at' => now()->subHour(),
    ]);

    actingAs($user);

    $component = livewire(\App\Livewire\Chats\Show::class, ['chat' => $chat]);
    $messages = $component->get('messages');

    expect($messages)->toHaveCount(3)
        ->and($messages[0]->parts['text'])->toBe('First message')
        ->and($messages[1]->parts['text'])->toBe('Second message')
        ->and($messages[2]->parts['text'])->toBe('Third message');
});

it('prevents unauthorized users from sending messages', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $chat = Chat::factory()->recycle($owner)->create(['visibility' => 'public']);

    actingAs($otherUser);

    livewire(\App\Livewire\Chats\Show::class, ['chat' => $chat])
        ->set('newMessage', 'Unauthorized message')
        ->call('sendMessage')
        ->assertForbidden();
});

it('prevents unauthorized users from changing visibility', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $chat = Chat::factory()->recycle($owner)->create(['visibility' => 'public']);

    actingAs($otherUser);

    livewire(\App\Livewire\Chats\Show::class, ['chat' => $chat])
        ->call('share')
        ->assertForbidden();

    livewire(\App\Livewire\Chats\Show::class, ['chat' => $chat])
        ->call('unshare')
        ->assertForbidden();
});

it('prevents unauthorized users from changing model', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $chat = Chat::factory()->recycle($owner)->create(['visibility' => 'public']);

    actingAs($otherUser);

    livewire(\App\Livewire\Chats\Show::class, ['chat' => $chat])
        ->call('setModel', 'gpt-4o')
        ->assertForbidden();
});

it('mounts with correct model from chat', function (): void {
    $user = User::factory()->create();
    $chat = Chat::factory()->recycle($user)->create(['model' => 'gpt-4']);

    actingAs($user);

    livewire(\App\Livewire\Chats\Show::class, ['chat' => $chat])
        ->assertSet('model', 'gpt-4');
});
