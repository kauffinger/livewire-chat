<?php

namespace App\Livewire;

use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class Chat extends Component
{
    public array $messages = [];

    public string $newMessage = '';

    public function sendMessage(): void
    {
        if (trim($this->newMessage) === '') {
            return;
        }

        $this->messages[] = new \App\Dtos\UserMessage($this->newMessage);
        $this->newMessage = '';

        $this->js('$wire.runChatToolLoop()');
    }


    public function runChatToolLoop(): void
    {
        $generator = Prism::text()
            ->using(Provider::OpenAI, 'gpt-4o-mini')
            ->withSystemPrompt('You are a helpful assistant.')
            ->withMessages(collect($this->messages)->map->toPrism()->all())
            ->asStream();

        $message = '';
        foreach ($generator as $chunk) {
            $message .= $chunk->text;

            $this->stream('streamed-message', Str::markdown($message), true);
        }

        $this->messages[] = new \App\Dtos\AssistantMessage($message);
    }

    public function render(): View
    {
        return view('livewire.chat');
    }
}
