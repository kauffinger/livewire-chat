<?php

use App\Ai\Agents\ChatAgent;
use App\Livewire\Chats\Show;
use App\Models\User;
use Database\Factories\ConversationFactory;
use Database\Factories\ConversationMessageFactory;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

it('renders the conversation page for its owner', function (): void {
    $user = User::factory()->create();
    $conversation = ConversationFactory::new()
        ->recycle($user)
        ->withMessages()
        ->create();

    actingAs($user);

    get(route('conversation.show', $conversation))
        ->assertOk()
        ->assertSeeLivewire(Show::class);
});

it('forbids the conversation page for other users', function (): void {
    $conversation = ConversationFactory::new()->create();

    actingAs(User::factory()->create());

    get(route('conversation.show', $conversation))->assertForbidden();
});

it('redirects guests from the conversation page to the login page', function (): void {
    $conversation = ConversationFactory::new()->create();

    get(route('conversation.show', $conversation))->assertRedirect(route('login'));
});

it('shows user\'s conversation', function (): void {
    $user = User::factory()->create();
    $conversation = ConversationFactory::new()
        ->recycle($user)
        ->withMessages()
        ->create();

    actingAs($user);

    livewire(Show::class, ['conversation' => $conversation])
        ->assertOk();
});

it('disallows other users to show the conversation', function (): void {
    $user = User::factory()->create();
    $conversation = ConversationFactory::new()
        ->recycle($user)
        ->withMessages()
        ->create();

    $otherUser = User::factory()->create();
    actingAs($otherUser);

    livewire(Show::class, ['conversation' => $conversation])
        ->assertForbidden();
});

it('disallows guests to show the conversation', function (): void {
    $user = User::factory()->create();
    $conversation = ConversationFactory::new()
        ->recycle($user)
        ->withMessages()
        ->create();

    livewire(Show::class, ['conversation' => $conversation])
        ->assertForbidden();
});

it('contains the messages', function (): void {
    $user = User::factory()->create();
    $conversation = ConversationFactory::new()
        ->recycle($user)
        ->withMessages()
        ->create();

    actingAs($user);

    $firstMessage = $conversation->messages()->orderBy('id')->first();

    livewire(Show::class, ['conversation' => $conversation])
        ->assertOk()
        ->assertSeeText($firstMessage->content);
});

it('allows sending messages', function (): void {
    ChatAgent::fake(['Hello, human!']);

    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create();

    actingAs($user);

    livewire(Show::class, ['conversation' => $conversation])
        ->set('newMessage', 'Hello, world!')
        ->call('sendMessage')
        ->assertSet('newMessage', '')
        ->assertSet('pendingMessage', 'Hello, world!')
        ->call('runAgent')
        ->assertSet('pendingMessage', '')
        ->assertDispatched('conversation-updated');

    ChatAgent::assertPrompted('Hello, world!');

    expect($conversation->messages()->orderBy('id')->get())
        ->toHaveCount(2)
        ->sequence(
            fn ($message) => $message->role->toBe('user')->content->toBe('Hello, world!'),
            fn ($message) => $message->role->toBe('assistant')->content->toBe('Hello, human!'),
        );
});

it('does not send empty messages', function (): void {
    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create();

    actingAs($user);

    livewire(Show::class, ['conversation' => $conversation])
        ->set('newMessage', '')
        ->call('sendMessage')
        ->assertSet('pendingMessage', '');
});

it('trims whitespace from messages', function (): void {
    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create();

    actingAs($user);

    livewire(Show::class, ['conversation' => $conversation])
        ->set('newMessage', '   ')
        ->call('sendMessage')
        ->assertSet('pendingMessage', '')
        ->set('newMessage', '  Hello, world!  ')
        ->call('sendMessage')
        ->assertSet('pendingMessage', 'Hello, world!');
});

it('does not run the agent without a pending message', function (): void {
    ChatAgent::fake();

    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create();

    actingAs($user);

    livewire(Show::class, ['conversation' => $conversation])
        ->call('runAgent');

    ChatAgent::assertNeverPrompted();
});

it('titles the conversation after its first message', function (): void {
    ChatAgent::fake();

    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create(['title' => 'New chat']);

    actingAs($user);

    livewire(Show::class, ['conversation' => $conversation])
        ->set('newMessage', 'What is the airspeed velocity of an unladen swallow, roughly speaking?')
        ->call('sendMessage')
        ->call('runAgent')
        ->set('newMessage', 'African or European?')
        ->call('sendMessage')
        ->call('runAgent');

    expect($conversation->refresh()->title)->toBe('What is the airspeed velocity of an unladen...');
});

it('loads messages in chronological order', function (): void {
    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create();

    $firstMessage = ConversationMessageFactory::new()->for($conversation)->create(['content' => 'First message']);
    $secondMessage = ConversationMessageFactory::new()->for($conversation)->assistant()->create(['content' => 'Second message']);
    $thirdMessage = ConversationMessageFactory::new()->for($conversation)->create(['content' => 'Third message']);

    actingAs($user);

    $component = livewire(Show::class, ['conversation' => $conversation])
        ->assertSeeHtmlInOrder(['First message', 'Second message', 'Third message']);

    $messages = $component->get('messages');

    expect($messages)->toHaveCount(3)
        ->and($messages[0]['id'])->toBe($firstMessage->id)
        ->and($messages[0]['content'])->toBe('First message')
        ->and($messages[1]['id'])->toBe($secondMessage->id)
        ->and($messages[1]['content'])->toBe('Second message')
        ->and($messages[2]['id'])->toBe($thirdMessage->id)
        ->and($messages[2]['content'])->toBe('Third message');
});

it('renders stable message keys and keeps pending messages flush with history', function (): void {
    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create();

    $historyUserMessage = ConversationMessageFactory::new()->for($conversation)->create();
    $historyAssistantMessage = ConversationMessageFactory::new()->for($conversation)->assistant()->create();

    actingAs($user);

    livewire(Show::class, ['conversation' => $conversation])
        ->assertDontSeeHtml('wire:key="pending-user-message"')
        ->set('newMessage', 'Pending follow-up question')
        ->call('sendMessage')
        ->assertSeeHtmlInOrder([
            sprintf('wire:key="message-%s"', $historyUserMessage->id),
            sprintf('wire:key="message-%s"', $historyAssistantMessage->id),
            'wire:key="pending-user-message"',
            'Pending follow-up question',
            'wire:key="streaming-message-2"',
        ])
        ->assertDontSeeHtml('flex-1 flex w-full flex-row justify-start');
});

it('shows the tool calls of stored assistant messages', function (): void {
    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create();

    ConversationMessageFactory::new()->for($conversation)->assistant()->create([
        'content' => 'That makes 5.',
        'steps' => [
            [
                'content' => '',
                'tool_calls' => [
                    ['id' => 'call_1', 'name' => 'Sum', 'arguments' => ['a' => 2, 'b' => 3], 'result' => '5'],
                ],
            ],
            ['content' => 'That makes 5.', 'tool_calls' => []],
        ],
    ]);

    actingAs($user);

    $component = livewire(Show::class, ['conversation' => $conversation])
        ->assertSee('"name":"Sum"');

    expect($component->get('messages')[0]['toolCalls'])
        ->toHaveCount(1)
        ->and($component->get('messages')[0]['toolCalls'][0]['name'])->toBe('Sum');
});
