<?php

namespace App\Actions;

use Illuminate\Support\Arr;
use Laravel\Ai\Streaming\Events\ReasoningDelta;
use Laravel\Ai\Streaming\Events\StreamEvent;
use Laravel\Ai\Streaming\Events\StreamStart;
use Laravel\Ai\Streaming\Events\TextDelta;
use Laravel\Ai\Streaming\Events\ToolCall;
use Laravel\Ai\Streaming\Events\ToolResult;

final class UpdateStreamDataFromAiEvent
{
    /**
     * @return array{text: string, thinking: string, toolCalls: list<array<string, mixed>>, currentChunkType: string}
     */
    public function initial(): array
    {
        return [
            'text' => '',
            'thinking' => '',
            'toolCalls' => [],
            'currentChunkType' => 'text',
        ];
    }

    /**
     * Fold a stream event into the turn so far, in the shape the AI SDK stores a finished turn in.
     *
     * @param  array{text: string, thinking: string, toolCalls: list<array<string, mixed>>, currentChunkType: string}  $streamData
     * @return array{text: string, thinking: string, toolCalls: list<array<string, mixed>>, currentChunkType: string}
     */
    public function handle(array $streamData, StreamEvent $event): array
    {
        if ($event instanceof StreamStart && trim($streamData['text']) !== '') {
            $streamData['text'] .= "\n\n";
        } elseif ($event instanceof TextDelta) {
            $streamData['text'] .= $event->delta;
        } elseif ($event instanceof ReasoningDelta) {
            $streamData['thinking'] .= $event->delta;
        } elseif ($event instanceof ToolCall) {
            $streamData['toolCalls'][] = Arr::only($event->toolCall->toArray(), ['id', 'name', 'arguments']);
        } elseif ($event instanceof ToolResult) {
            $streamData['toolCalls'] = array_map(
                fn (array $toolCall): array => $toolCall['id'] === $event->toolResult->id
                    ? [...$toolCall, ...Arr::only($event->toolResult->toArray(), ['result', 'denied', 'failed'])]
                    : $toolCall,
                $streamData['toolCalls'],
            );
        }

        $streamData['currentChunkType'] = $event->type();

        return $streamData;
    }
}
