<?php

namespace App\Dtos;

use Livewire\Wireable;

class AssistantMessage extends \Prism\Prism\ValueObjects\Messages\AssistantMessage implements Wireable
{
    public function toLivewire(): array
    {
        return [
            'content' => $this->content,
            // tool calls not implemented
        ];
    }

    public static function fromLivewire($value): self
    {
        return new self($value['content'], [], []);
    }

    public function fromPrism(\Prism\Prism\ValueObjects\Messages\AssistantMessage $message): self
    {
        return new self($message->content, $message->toolCalls, $message->additionalContent);
    }

    public function toPrism(): \Prism\Prism\ValueObjects\Messages\AssistantMessage
    {
        return new \Prism\Prism\ValueObjects\Messages\AssistantMessage($this->content, $this->toolCalls, $this->additionalContent);
    }
}
