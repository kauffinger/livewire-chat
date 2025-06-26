<?php

namespace App\Dtos;

final class StreamData
{
    public function __construct(
        public string $text = '',
        public string $thinking = '',
        public array $meta = [],
        public array $toolCalls = [],
        public array $toolResults = []
    ) {}

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'thinking' => $this->thinking,
            'meta' => $this->meta,
            'toolCalls' => $this->toolCalls,
            'toolResults' => $this->toolResults,
        ];
    }
}
