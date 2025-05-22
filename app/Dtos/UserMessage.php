<?php

namespace App\Dtos;

use Livewire\Wireable;

class UserMessage extends \Prism\Prism\ValueObjects\Messages\UserMessage implements Wireable
{
    public function toLivewire(): array
    {
        return [
            'content' => $this->content,
        ];
    }

    public static function fromLivewire($value): self
    {
        return new self($value['content']);
    }

    public function fromPrism(\Prism\Prism\ValueObjects\Messages\UserMessage $message): self
    {
        return new self($message->content);
    }

    public function toPrism(): \Prism\Prism\ValueObjects\Messages\UserMessage
    {
        return new \Prism\Prism\ValueObjects\Messages\UserMessage($this->content);
    }
}
