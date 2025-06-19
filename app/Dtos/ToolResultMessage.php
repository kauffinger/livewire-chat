<?php

namespace App\Dtos;

use Livewire\Wireable;
use Prism\Prism\ValueObjects\ToolResult;

readonly class ToolResultMessage extends \Prism\Prism\ValueObjects\Messages\ToolResultMessage implements Wireable
{
    public function toLivewire(): array
    {
        return [
            'toolResults' => $this->toolResults,
        ];
    }

    public static function fromLivewire($value): self
    {
        return new self($value['toolResults'] ?? []);
    }

    public function fromPrism(\Prism\Prism\ValueObjects\Messages\ToolResultMessage $message): self
    {
        return new self($message->toolResults);
    }

    public function toPrism(): \Prism\Prism\ValueObjects\Messages\ToolResultMessage
    {
        $prismToolResults = is_array($this->toolResults) ? $this->mapToolResultsToPrism($this->toolResults) : $this->toolResults;

        return new \Prism\Prism\ValueObjects\Messages\ToolResultMessage($prismToolResults);
    }

    private function mapToolResultsToPrism(array $toolResults): array
    {
        return array_map(function (array $toolResult) {
            return new ToolResult(
                $toolResult['toolCallId'] ?? '',
                $toolResult['toolName'] ?? '',
                $toolResult['args'] ?? [],
                $toolResult['result'] ?? null,
                $toolResult['toolCallResultId'] ?? null
            );
        }, $toolResults);
    }
}
