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
        match ($chunk->chunkType) {
            ChunkType::ToolCall => $this->handleToolCall($streamData, $chunk),
            ChunkType::ToolResult => $this->handleToolResult($streamData, $chunk),
            ChunkType::Meta => $this->handleMeta($streamData, $chunk),
            default => $this->handleText($streamData, $chunk),
        };
    }

    protected function handleToolCall(StreamData $streamData, Chunk $chunk): void
    {
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
    }

    protected function handleToolResult(StreamData $streamData, Chunk $chunk): void
    {
        foreach ($chunk->toolResults as $toolResult) {
            $streamData->toolResults[] = [
                'result' => $toolResult->result ?? '',
                'toolName' => $toolResult->toolName ?? 'unknown',
                'toolCallId' => $toolResult->toolCallId ?? null,
                'args' => $toolResult->args ?? [],
                'toolCallResultId' => $toolResult->toolCallResultId ?? null,
            ];
        }
    }

    protected function handleMeta(StreamData $streamData, Chunk $chunk): void
    {
        if ($chunk->meta instanceof Meta) {
            $streamData->meta[] = $chunk->meta;
        }
    }

    protected function handleText(StreamData $streamData, Chunk $chunk): void
    {
        $streamData->{$chunk->chunkType->value} .= $chunk->text;
    }
}
