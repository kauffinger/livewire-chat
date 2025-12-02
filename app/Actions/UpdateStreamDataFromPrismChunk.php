<?php

namespace App\Actions;

use App\Dtos\StreamData;
use Prism\Prism\Streaming\Events\StreamEvent;
use Prism\Prism\Streaming\Events\TextDeltaEvent;
use Prism\Prism\Streaming\Events\ThinkingEvent;
use Prism\Prism\Streaming\Events\ToolCallEvent;
use Prism\Prism\Streaming\Events\ToolResultEvent;
use Prism\Prism\Text\Chunk;

class UpdateStreamDataFromPrismChunk
{
    public function handle(StreamData $streamData, StreamEvent $event): void
    {
        switch ($event::class) {
            case ToolCallEvent::class:
                // Extract tool calls from the chunk's toolCalls array and add to accumulated data
                $streamData->toolCalls[] = [
                    'name' => $event->toolCall->name ?? 'unknown',
                    'id' => $event->toolCall->id ?? null,
                    'arguments' => $event->toolCall->arguments() ?? [],
                    'reasoningId' => $event->reasoningId ?? null,
                    'resultId' => $event->resultId ?? null,
                    'reasoningSummary' => $event->reasoningSummary ?? null,
                ];
                break;
            case ToolResultEvent::class:
                $streamData->toolResults[] = [
                    'result' => $event->toolResult->result ?? '',
                    'toolName' => $event->toolResult->toolName ?? 'unknown',
                    'toolCallId' => $event->toolResult->toolCallId ?? null,
                    'args' => $event->toolResult->args ?? [],
                    'toolCallResultId' => $event->toolResult->toolCallResultId ?? null,
                ];
                break;

            case TextDeltaEvent::class:
                $streamData->text .= $event->delta;
                break;

            case ThinkingEvent::class:
                $streamData->thinking .= $event->delta;
                break;
        }
    }
}
