<?php

namespace App\Dtos;

use Livewire\Wireable;
use Prism\Prism\ValueObjects\ToolCall;

class AssistantMessage extends \Prism\Prism\ValueObjects\Messages\AssistantMessage implements Wireable
{
    public function toLivewire(): array
    {
        return [
            'content' => $this->content,
            'toolCalls' => $this->toolCalls,
            'additionalContent' => $this->additionalContent,
        ];
    }

    public static function fromLivewire($value): self
    {
        return new self(
            $value['content'],
            $value['toolCalls'] ?? [],
            $value['additionalContent'] ?? []
        );
    }

    public function fromPrism(\Prism\Prism\ValueObjects\Messages\AssistantMessage $message): self
    {
        return new self($message->content, $message->toolCalls, $message->additionalContent);
    }

    public function toPrism(): \Prism\Prism\ValueObjects\Messages\AssistantMessage
    {
        $prismToolCalls = is_array($this->toolCalls) ? $this->mapToolCallsToPrism($this->toolCalls) : $this->toolCalls;

        return new \Prism\Prism\ValueObjects\Messages\AssistantMessage($this->content, $prismToolCalls, $this->additionalContent);
    }

    private function mapToolCallsToPrism(array $toolCalls): array
    {
        return array_map(function (array $toolCall) {
            return new ToolCall(
                $toolCall['id'] ?? '',
                $toolCall['name'] ?? '',
                $toolCall['arguments'] ?? [],
                $toolCall['resultId'] ?? null,
                $toolCall['reasoningId'] ?? null,
                $toolCall['reasoningSummary'] ?? null
            );
        }, $toolCalls);
    }
}
