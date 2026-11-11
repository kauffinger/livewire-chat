<?php

use App\Ai\Agents\ChatAgent;
use App\Livewire\Chats\Show;
use App\Models\User;
use Database\Factories\ConversationFactory;
use Database\Factories\ConversationMessageFactory;
use Illuminate\Support\Facades\Exceptions;
use Laravel\Ai\Approvals\PendingApproval;
use Laravel\Ai\Enums\MessageStatus;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Responses\AgentResponse;

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

    $component = livewire(Show::class, ['conversation' => $conversation])
        ->set('newMessage', 'Hello, world!')
        ->call('sendMessage')
        ->assertSet('newMessage', '')
        ->assertSet('pendingMessage', 'Hello, world!')
        ->call('runAgent')
        ->assertSet('pendingMessage', '')
        ->assertSeeHtml('wire:key="streaming-message-1"')
        ->assertDispatched('conversation-updated');

    expect($component->effects['islandFragments'])
        ->toHaveCount(1)
        ->and($component->effects['islandFragments'][0])
        ->toContain('name=messages')
        ->toContain('Hello, human!');

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

it('renders the new conversation page', function (): void {
    actingAs(User::factory()->create());

    get(route('conversation.create'))
        ->assertOk()
        ->assertSeeLivewire(Show::class)
        ->assertSeeText('What can I help you with?');
});

it('redirects guests from the new conversation page to the login page', function (): void {
    get(route('conversation.create'))->assertRedirect(route('login'));
});

it('does not store a conversation before the first message', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    livewire(Show::class)->assertOk();

    expect($user->conversations()->count())->toBe(0);
});

it('starts a conversation with the first message', function (): void {
    config(['ai.conversations.generate_title' => false]);
    ChatAgent::fake(['Hello, human!']);

    $user = User::factory()->create();

    actingAs($user);

    $component = livewire(Show::class)
        ->set('newMessage', 'Hello, world!')
        ->call('sendMessage')
        ->call('runAgent');

    $conversation = $user->conversations()->sole();

    $component->assertRedirect(route('conversation.show', $conversation));

    expect($conversation->title)->toBe('Hello, world!')
        ->and($conversation->messages()->orderBy('id')->pluck('content')->all())
        ->toBe(['Hello, world!', 'Hello, human!']);
});

it('gives the message back when the assistant fails', function (): void {
    Exceptions::fake();
    ChatAgent::fake(fn () => throw new RuntimeException('The provider is down.'));

    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create();

    actingAs($user);

    livewire(Show::class, ['conversation' => $conversation])
        ->set('newMessage', 'Hello, world!')
        ->call('sendMessage')
        ->call('runAgent')
        ->assertSet('newMessage', 'Hello, world!')
        ->assertSet('pendingMessage', '')
        ->assertDispatched(
            'toast-show',
            slots: ['text' => 'Please try again.', 'heading' => 'The assistant could not respond.'],
            dataset: ['variant' => 'danger'],
        )
        ->assertNotDispatched('conversation-updated');

    Exceptions::assertReported(RuntimeException::class);

    expect($conversation->messages()->count())->toBe(0);
});

it('escapes html in the reply while it streams', function (): void {
    ChatAgent::fake(['<img src=x onerror=alert(1)> & more']);

    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create();

    actingAs($user);

    $streamed = '';

    ob_start(function (string $buffer) use (&$streamed): string {
        $streamed .= $buffer;

        return '';
    });

    livewire(Show::class, ['conversation' => $conversation])
        ->set('newMessage', 'Show me an image tag.')
        ->call('sendMessage')
        ->call('runAgent');

    ob_end_clean();

    expect($streamed)
        ->toContain('u003Cimg')
        ->toContain('u0026')
        ->not->toContain('<img');
});

it('keeps the line breaks of a user message and escapes its html', function (): void {
    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create();

    ConversationMessageFactory::new()->for($conversation)->create([
        'content' => "First line\n<b>Second line</b>",
    ]);

    actingAs($user);

    livewire(Show::class, ['conversation' => $conversation])
        ->assertSeeHtml("First line<br />\n&lt;b&gt;Second line&lt;/b&gt;")
        ->assertDontSeeHtml('<b>Second line</b>');
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
        ->assertSeeHtmlInOrder([
            sprintf('wire:key="message-%s"', $historyUserMessage->id),
            sprintf('wire:key="message-%s"', $historyAssistantMessage->id),
            'wire:key="streaming-message-0"',
        ])
        ->set('newMessage', 'Pending follow-up question')
        ->call('sendMessage')
        ->assertSeeHtmlInOrder([
            'name=messages',
            'wire:key="pending-user-message"',
            'Pending follow-up question',
            'wire:key="streaming-message-0"',
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

it('shows the reasoning of stored assistant messages', function (): void {
    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create();

    ConversationMessageFactory::new()->for($conversation)->assistant()->create([
        'content' => 'That makes 5.',
        'steps' => [
            ['content' => 'That makes 5.', 'tool_calls' => [], 'reasoning' => 'Two plus three is five.'],
        ],
    ]);

    actingAs($user);

    livewire(Show::class, ['conversation' => $conversation])
        ->assertSee('Two plus three is five.');
});

it('marks a reply the assistant could not finish', function (): void {
    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create();

    ConversationMessageFactory::new()->for($conversation)->assistant()->create([
        'content' => 'Finished reply',
    ]);

    actingAs($user);

    livewire(Show::class, ['conversation' => $conversation])
        ->assertDontSeeText('The assistant could not finish this response.');

    ConversationMessageFactory::new()->for($conversation)->assistant()->create([
        'content' => 'Unfinished reply',
        'status' => MessageStatus::Failed,
    ]);

    livewire(Show::class, ['conversation' => $conversation])
        ->assertSeeText('The assistant could not finish this response.');
});

it('shows the latest messages and loads earlier ones on request', function (): void {
    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create();

    ConversationMessageFactory::new()->for($conversation)->create(['content' => 'The very first message']);
    ConversationMessageFactory::new()->for($conversation)->count(20)->create();

    actingAs($user);

    $component = livewire(Show::class, ['conversation' => $conversation])
        ->assertDontSeeText('The very first message')
        ->assertSeeHtml('wire:intersect="showEarlierMessages"');

    expect($component->get('messages'))->toHaveCount(20);

    $component->call('showEarlierMessages');

    expect($component->get('messages'))
        ->toHaveCount(21)
        ->and($component->get('messages')[0]['content'])->toBe('The very first message')
        ->and($component->get('history')->hasMorePages())->toBeFalse();
});

it('asks for approval before a tool call that needs it', function (): void {
    ChatAgent::fake([
        AgentResponse::fakeWithPendingApprovals([
            new PendingApproval(
                id: 'call_1',
                tool: 'UpdateProfileName',
                arguments: ['name' => 'Ada King'],
                reason: 'This will change the name on your profile.',
            ),
        ]),
    ]);

    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create();

    actingAs($user);

    livewire(Show::class, ['conversation' => $conversation])
        ->assertSeeHtml('wire:model="newMessage"')
        ->set('newMessage', 'Call me Ada King.')
        ->call('sendMessage')
        ->call('runAgent')
        ->assertSeeText('Approval needed')
        ->assertSeeText('UpdateProfileName')
        ->assertSeeText('This will change the name on your profile.')
        ->assertSeeText('Ada King')
        ->assertDontSeeHtml('wire:model="newMessage"')
        ->set('newMessage', 'Never mind.')
        ->call('sendMessage')
        ->assertSet('pendingMessage', '');
});

it('resumes the turn with the decision on its tool calls', function (string $action, bool $approved): void {
    ChatAgent::fake([
        AgentResponse::fakeWithPendingApprovals([
            new PendingApproval(id: 'call_1', tool: 'UpdateProfileName', arguments: ['name' => 'Ada King']),
        ]),
        'All done.',
    ]);

    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create();

    actingAs($user);

    livewire(Show::class, ['conversation' => $conversation])
        ->set('newMessage', 'Call me Ada King.')
        ->call('sendMessage')
        ->call('runAgent')
        ->call($action)
        ->assertDontSeeText('Approval needed')
        ->assertSeeHtml('wire:model="newMessage"')
        ->assertDispatched('conversation-updated');

    ChatAgent::assertPrompted(
        fn (AgentPrompt $prompt): bool => $prompt->approvalDecisions?->get('*')?->isApproved() === $approved,
    );

    expect($conversation->messages()->orderBy('id')->get())
        ->toHaveCount(2)
        ->sequence(
            fn ($message) => $message->role->toBe('user'),
            fn ($message) => $message->role->toBe('assistant')->status->toBe(MessageStatus::Completed),
        );
})->with([
    'approved' => ['approveToolCalls', true],
    'rejected' => ['rejectToolCalls', false],
]);

it('ignores a decision when no tool call awaits approval', function (string $action): void {
    ChatAgent::fake();

    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->withMessages()->create();

    actingAs($user);

    livewire(Show::class, ['conversation' => $conversation])
        ->call($action);

    ChatAgent::assertNeverPrompted();
})->with(['approveToolCalls', 'rejectToolCalls']);
