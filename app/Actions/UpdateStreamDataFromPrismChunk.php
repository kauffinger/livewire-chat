<?php

namespace App\Actions;

use App\Dtos\StreamData;
use Prism\Prism\Enums\ChunkType;
use Prism\Prism\Text\Chunk;
use Prism\Prism\ValueObjects\Meta;

class UpdateStreamDataFromPrismChunk
{
    public function handle(StreamData $streamData, Chunk $chunk): void
    {
        switch ($chunk->chunkType) {
            case ChunkType::ToolCall:
                // Extract tool calls from the chunk's toolCalls array and add to accumulated data
                foreach ($chunk->toolCalls as $toolCall) {
                    $streamData->toolCalls[] = [
                        'name' => $toolCall->name ?? 'unknown',
                        'id' => $toolCall->id ?? null,
                        'arguments' => $toolCall->arguments() ?? [],
                        'reasoningId' => $toolCall->reasoningId ?? null,
                        'resultId' => $toolCall->resultId ?? null,
                        'reasoningSummary' => $toolCall->reasoningSummary ?? null,
                    ];
                }
                break;
            case ChunkType::ToolResult:
                // Extract tool results from the chunk's toolResults array and add to accumulated data
                foreach ($chunk->toolResults as $toolResult) {
                    $toolResultData = [
                        'result' => $toolResult->result ?? '',
                        'toolName' => $toolResult->toolName ?? 'unknown',
                        'toolCallId' => $toolResult->toolCallId ?? null,
                        'args' => $toolResult->args ?? [],
                        'toolCallResultId' => $toolResult->toolCallResultId ?? null,
                    ];
                    $streamData->toolResults[] = $toolResultData;
                }
                break;
            case ChunkType::Meta:
                if ($chunk->meta instanceof Meta) {
                    $streamData->meta[] = $chunk->meta;
                }
                break;
            default:
                $streamData->{$chunk->chunkType->value} .= $chunk->text;
        }
    }
}
