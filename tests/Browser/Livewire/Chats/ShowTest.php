<?php

use App\Ai\Agents\ChatAgent;
use App\Models\User;
use Database\Factories\ConversationFactory;
use Database\Factories\ConversationMessageFactory;
use Illuminate\Support\Facades\Exceptions;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    ob_start(fn (string $output): string => preg_replace('/\{"stream":true.*?"endStream":true\}/', '', $output));
});

afterEach(function (): void {
    ob_end_flush();
});

it('starts a conversation with the first message', function (): void {
    config(['ai.conversations.generate_title' => false]);
    ChatAgent::fake(['Hello, **human**!']);

    $user = User::factory()->create();

    actingAs($user);

    $page = visit(route('conversation.create'))
        ->assertSee('What can I help you with?')
        ->type('newMessage', 'Hello, world!')
        ->keys('newMessage', 'Enter')
        ->assertSourceHas('Hello, <strong>human</strong>!');

    $conversation = $user->conversations()->sole();
    $conversationPath = route('conversation.show', $conversation, absolute: false);

    $page->assertPathIs($conversationPath)
        ->assertVisible("[data-flux-sidebar] a[href$=\"{$conversationPath}\"]")
        ->assertNoJavaScriptErrors();

    expect($conversation->messages()->orderBy('id')->pluck('content')->all())
        ->toBe(['Hello, world!', 'Hello, **human**!']);
});

it('sends a suggestion as the first message', function (): void {
    config(['ai.conversations.generate_title' => false]);
    ChatAgent::fake(['That makes 42.5.']);

    actingAs(User::factory()->create());

    visit(route('conversation.create'))
        ->click('Add 12.5 and 30 with the sum tool')
        ->assertSee('That makes 42.5.')
        ->assertNoJavaScriptErrors();

    ChatAgent::assertPrompted('Add 12.5 and 30 with the sum tool');
});

it('adds a new line with shift enter instead of sending', function (): void {
    ChatAgent::fake(['Got both lines.']);

    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create();

    actingAs($user);

    visit(route('conversation.show', $conversation))
        ->type('newMessage', 'First line')
        ->keys('newMessage', 'Shift+Enter')
        ->assertValue('newMessage', "First line\n")
        ->append('newMessage', 'Second line')
        ->keys('newMessage', 'Enter')
        ->assertSee('Got both lines.')
        ->assertNoJavaScriptErrors();

    ChatAgent::assertPrompted("First line\nSecond line");
});

it('gives the message back when the assistant fails', function (): void {
    Exceptions::fake();
    ChatAgent::fake(fn () => throw new RuntimeException('The provider is down.'));

    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create();

    actingAs($user);

    visit(route('conversation.show', $conversation))
        ->type('newMessage', 'Hello, world!')
        ->keys('newMessage', 'Enter')
        ->assertSee('The assistant could not respond.')
        ->assertValue('newMessage', 'Hello, world!')
        ->assertDontSee('Hello, world!')
        ->assertNoJavaScriptErrors();

    Exceptions::assertReported(RuntimeException::class);
});

it('renders the markdown of a stored reply', function (): void {
    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create();

    ConversationMessageFactory::new()->for($conversation)->assistant()->create([
        'content' => "Run **this**:\n\n```php\necho 'Hello';\n```",
    ]);

    actingAs($user);

    visit(route('conversation.show', $conversation))
        ->assertSourceHas('Run <strong>this</strong>:')
        ->assertSourceHas('<span class="hljs-keyword">echo</span>')
        ->assertSeeIn('.code-block-header', 'php')
        ->assertNoJavaScriptErrors();
});

it('shows the html of a stored reply as text', function (): void {
    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create();

    ConversationMessageFactory::new()->for($conversation)->assistant()->create([
        'content' => '<img src=x onerror=alert(1)> & more',
    ]);

    actingAs($user);

    visit(route('conversation.show', $conversation))
        ->assertSee('<img src=x onerror=alert(1)> & more')
        ->assertNotPresent('article img')
        ->assertNoJavaScriptErrors();
});

it('shows the arguments of a tool call when it is opened', function (): void {
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

    visit(route('conversation.show', $conversation))
        ->assertSee('That makes 5.')
        ->assertDontSee('{"a":2,"b":3}')
        ->click('Sum')
        ->assertSee('{"a":2,"b":3}')
        ->assertNoJavaScriptErrors();
});

it('loads earlier messages when scrolling up', function (): void {
    $user = User::factory()->create();
    $conversation = ConversationFactory::new()->recycle($user)->create();

    ConversationMessageFactory::new()->for($conversation)->create(['content' => 'The very first message']);
    ConversationMessageFactory::new()->for($conversation)->count(20)->create();

    actingAs($user);

    $page = visit(route('conversation.show', $conversation))
        ->assertDontSee('The very first message');

    $page->script('document.querySelector(\'[x-ref="messages"]\').scrollTo({ top: -document.body.scrollHeight * 100 })');

    $page->assertSee('The very first message')
        ->assertNoJavaScriptErrors();
});
