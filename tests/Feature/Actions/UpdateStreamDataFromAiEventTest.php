<?php

use App\Actions\UpdateStreamDataFromAiEvent;
use Laravel\Ai\Responses\Data\ToolCall as ToolCallData;
use Laravel\Ai\Responses\Data\ToolResult as ToolResultData;
use Laravel\Ai\Streaming\Events\ReasoningDelta;
use Laravel\Ai\Streaming\Events\StreamStart;
use Laravel\Ai\Streaming\Events\TextDelta;
use Laravel\Ai\Streaming\Events\ToolCall;
use Laravel\Ai\Streaming\Events\ToolResult;

it('records a tool call and puts its result on that call', function (): void {
    $action = new UpdateStreamDataFromAiEvent;
    $streamData = $action->initial();

    $streamData = $action->handle(
        $streamData,
        new ToolCall(
            'event-tool-call',
            new ToolCallData(
                id: 'tool-call-id',
                name: 'search_docs',
                arguments: ['query' => 'laravel'],
                resultId: 'tool-result-id',
                reasoningId: 'reasoning-id',
                reasoningEncryptedContent: 'encrypted-reasoning',
            ),
            timestamp: now()->timestamp,
        ),
    );

    $streamData = $action->handle(
        $streamData,
        new ToolCall(
            'event-other-tool-call',
            new ToolCallData(id: 'other-tool-call-id', name: 'sum', arguments: ['a' => 1, 'b' => 2]),
            timestamp: now()->timestamp,
        ),
    );

    expect($streamData['toolCalls'])->toBe([
        ['id' => 'tool-call-id', 'name' => 'search_docs', 'arguments' => ['query' => 'laravel']],
        ['id' => 'other-tool-call-id', 'name' => 'sum', 'arguments' => ['a' => 1, 'b' => 2]],
    ])->and($streamData['currentChunkType'])->toBe('tool_call');

    $streamData = $action->handle(
        $streamData,
        new ToolResult(
            'event-tool-result',
            new ToolResultData(
                id: 'tool-call-id',
                name: 'search_docs',
                arguments: ['query' => 'laravel'],
                result: ['results' => 3],
                resultId: 'tool-result-id',
            ),
            successful: true,
            error: null,
            timestamp: now()->timestamp,
        ),
    );

    expect($streamData['toolCalls'])->toBe([
        ['id' => 'tool-call-id', 'name' => 'search_docs', 'arguments' => ['query' => 'laravel'], 'result' => ['results' => 3]],
        ['id' => 'other-tool-call-id', 'name' => 'sum', 'arguments' => ['a' => 1, 'b' => 2]],
    ])->and($streamData['currentChunkType'])->toBe('tool_result');
});

it('marks a tool call the user rejected as denied', function (): void {
    $action = new UpdateStreamDataFromAiEvent;

    $streamData = $action->handle(
        $action->initial(),
        new ToolCall(
            'event-tool-call',
            new ToolCallData(id: 'tool-call-id', name: 'update_profile_name', arguments: ['name' => 'Ada']),
            timestamp: now()->timestamp,
        ),
    );

    $streamData = $action->handle(
        $streamData,
        new ToolResult(
            'event-tool-result',
            new ToolResultData(
                id: 'tool-call-id',
                name: 'update_profile_name',
                arguments: ['name' => 'Ada'],
                result: 'The user declined this action.',
                denied: true,
            ),
            successful: false,
            error: null,
            timestamp: now()->timestamp,
            denied: true,
        ),
    );

    expect($streamData['toolCalls'][0]['denied'])->toBeTrue();
});

it('maps reasoning deltas with laravel ai chunk type', function (): void {
    $action = new UpdateStreamDataFromAiEvent;
    $streamData = $action->initial();

    $streamData = $action->handle(
        $streamData,
        new ReasoningDelta(
            id: 'reasoning-event',
            reasoningId: 'reasoning-id',
            delta: 'Thinking...',
            timestamp: now()->timestamp,
        ),
    );

    expect($streamData['thinking'])->toBe('Thinking...')
        ->and($streamData['currentChunkType'])->toBe('reasoning_delta');
});

it('appends text deltas', function (): void {
    $action = new UpdateStreamDataFromAiEvent;
    $streamData = $action->initial();

    $streamData = $action->handle(
        $streamData,
        new TextDelta(
            id: 'text-event-1',
            messageId: 'message-id',
            delta: 'Hello',
            timestamp: now()->timestamp,
        ),
    );

    $streamData = $action->handle(
        $streamData,
        new TextDelta(
            id: 'text-event-2',
            messageId: 'message-id',
            delta: ' world',
            timestamp: now()->timestamp,
        ),
    );

    expect($streamData['text'])->toBe('Hello world')
        ->and($streamData['currentChunkType'])->toBe('text_delta');
});

it('separates the text of each step with a blank line', function (): void {
    $action = new UpdateStreamDataFromAiEvent;

    $events = [
        new StreamStart(id: 'start-1', provider: 'openai', model: 'gpt', timestamp: now()->timestamp),
        new TextDelta(id: 'text-1', messageId: 'message-1', delta: 'Let me add that up.', timestamp: now()->timestamp),
        new StreamStart(id: 'start-2', provider: 'openai', model: 'gpt', timestamp: now()->timestamp),
        new TextDelta(id: 'text-2', messageId: 'message-2', delta: 'That makes 5.', timestamp: now()->timestamp),
    ];

    $streamData = array_reduce($events, $action->handle(...), $action->initial());

    expect($streamData['text'])->toBe(TextDelta::combine($events))
        ->toBe("Let me add that up.\n\nThat makes 5.");
});
