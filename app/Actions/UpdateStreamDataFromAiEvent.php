<?php

namespace App\Actions;

use Laravel\Ai\Streaming\Events\ReasoningDelta;
use Laravel\Ai\Streaming\Events\StreamEvent;
use Laravel\Ai\Streaming\Events\TextDelta;
use Laravel\Ai\Streaming\Events\ToolCall;
use Laravel\Ai\Streaming\Events\ToolResult;

final class UpdateStreamDataFromAiEvent
{
    /**
     * @return array{text: string, thinking: string, toolCalls: array<int, array<string, mixed>>, toolResults: array<int, array<string, mixed>>, currentChunkType: string}
     */
    public function initial(): array
    {
        return [
            'text' => '',
            'thinking' => '',
            'toolCalls' => [],
            'toolResults' => [],
            'currentChunkType' => 'text',
        ];
    }

    /**
     * @param  array{text: string, thinking: string, toolCalls: array<int, array<string, mixed>>, toolResults: array<int, array<string, mixed>>, currentChunkType: string}  $streamData
     * @return array{text: string, thinking: string, toolCalls: array<int, array<string, mixed>>, toolResults: array<int, array<string, mixed>>, currentChunkType: string}
     */
    public function handle(array $streamData, StreamEvent $event): array
    {
        if ($event instanceof TextDelta) {
            $streamData['text'] .= $event->delta;
        } elseif ($event instanceof ReasoningDelta) {
            $streamData['thinking'] .= $event->delta;
        } elseif ($event instanceof ToolCall) {
            $streamData['toolCalls'][] = [
                'id' => $event->toolCall->id,
                'name' => $event->toolCall->name,
                'arguments' => $event->toolCall->arguments,
                'resultId' => $event->toolCall->resultId,
                'reasoningId' => $event->toolCall->reasoningId,
                'reasoningSummary' => $event->toolCall->reasoningSummary,
            ];
        } elseif ($event instanceof ToolResult) {
            $streamData['toolResults'][] = [
                'id' => $event->toolResult->id,
                'name' => $event->toolResult->name,
                'arguments' => $event->toolResult->arguments,
                'result' => $event->toolResult->result,
                'resultId' => $event->toolResult->resultId,
                'successful' => $event->successful,
                'error' => $event->error,
            ];
        }

        $streamData['currentChunkType'] = $event->type();

        return $streamData;
    }
}
