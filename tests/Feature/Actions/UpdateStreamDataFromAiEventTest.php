<?php

use App\Actions\UpdateStreamDataFromAiEvent;
use Laravel\Ai\Responses\Data\ToolCall as ToolCallData;
use Laravel\Ai\Responses\Data\ToolResult as ToolResultData;
use Laravel\Ai\Streaming\Events\ReasoningDelta;
use Laravel\Ai\Streaming\Events\TextDelta;
use Laravel\Ai\Streaming\Events\ToolCall;
use Laravel\Ai\Streaming\Events\ToolResult;

it('maps tool call and tool result events', function (): void {
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
                reasoningSummary: ['summary' => 'Need docs'],
            ),
            timestamp: now()->timestamp,
        ),
    );

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

    expect($streamData['toolCalls'])->toHaveCount(1)
        ->and($streamData['toolCalls'][0]['id'])->toBe('tool-call-id')
        ->and($streamData['toolCalls'][0]['name'])->toBe('search_docs')
        ->and($streamData['toolCalls'][0]['arguments'])->toBe(['query' => 'laravel'])
        ->and($streamData['toolCalls'][0]['resultId'])->toBe('tool-result-id')
        ->and($streamData['toolCalls'][0]['reasoningId'])->toBe('reasoning-id')
        ->and($streamData['toolCalls'][0]['reasoningSummary'])->toBe(['summary' => 'Need docs'])
        ->and($streamData['toolResults'])->toHaveCount(1)
        ->and($streamData['toolResults'][0]['id'])->toBe('tool-call-id')
        ->and($streamData['toolResults'][0]['name'])->toBe('search_docs')
        ->and($streamData['toolResults'][0]['arguments'])->toBe(['query' => 'laravel'])
        ->and($streamData['toolResults'][0]['result'])->toBe(['results' => 3])
        ->and($streamData['toolResults'][0]['resultId'])->toBe('tool-result-id')
        ->and($streamData['toolResults'][0]['successful'])->toBeTrue()
        ->and($streamData['toolResults'][0]['error'])->toBeNull()
        ->and($streamData['currentChunkType'])->toBe('tool_result');
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
