# Livewire Chat Kit

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kauffinger/livewire-chat-kit.svg?style=flat-square)](https://packagist.org/packages/kauffinger/livewire-chat-kit)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/kauffinger/livewire-chat/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/kauffinger/livewire-chat/actions?query=workflow%3Atests+branch%3Amain)
[![Linter Action Status](https://img.shields.io/github/actions/workflow/status/kauffinger/livewire-chat/lint.yml?branch=main&label=linter&style=flat-square)](https://github.com/kauffinger/livewire-chat/actions?query=workflow%3Alint+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/kauffinger/livewire-chat-kit.svg?style=flat-square)](https://packagist.org/packages/kauffinger/livewire-chat-kit)

A Laravel Starter Kit for building LLM-powered chat interfaces with Livewire, [FluxUI](https://fluxui.com), and [Prism](https://github.com/prismphp/prism). Jump right into the LLM party without touching (much) JavaScript, while still having a good-looking and effortlessly feeling chat interface.

This starter kit provides a clean, simple foundation for creating chat applications. It's designed to get you sending your first chat messages to an LLM instantly.

## Features

- **Livewire-Powered:** Build dynamic interfaces with PHP.
- **Streamed Responses:** Real-time message streaming from LLMs for a smooth UX.
- **FluxUI Components:** Beautiful, pre-built UI components for a polished look and feel.
- **Prism Integration:** The Laravel-way of speaking to LLMs. Easy to use, test, and switch between providers (e.g., OpenAI, Anthropic).
- **Minimal JavaScript:** Focus on your PHP backend.
- **TailwindCSS Styled:** Includes a TailwindCSS setup with a typography plugin for rendering markdown.

## Installation

You can install this starter kit into a new Laravel application using Composer:

```bash
laravel new my-chat-app --using=kauffinger/livewire-chat-kit
```

After installation, make sure to:

1.  Run migrations: `php artisan migrate`
2.  Install NPM dependencies: `npm install`
3.  Build assets: `npm run dev` (or `npm run build` for production)

## Getting Started

### 1. Configure LLM Provider

This starter kit uses [Prism](https://github.com/prismphp/prism) to interact with LLMs. By default, it's configured to use OpenAI's `gpt-4o-mini`. You'll need to add your API key to your `.env` file.

```env
OPENAI_API_KEY=your-openai-api-key
# OPENAI_ORGANIZATION_ID= (optional)
```

You can easily switch to other providers supported by Prism (like Anthropic) by modifying the `Chat.php` component. For example, to use Claude:

```php
// In app/Livewire/Chat.php
// ...
use Prism\Prism\Enums\Provider;

// ...
    public function runChatToolLoop(): void
    {
        $generator = Prism::text()
            ->using(Provider::Anthropic, 'claude-3-opus-20240229') // Example for Claude
            // ->using(Provider::OpenAI, 'gpt-4o-mini') // Default
            ->withSystemPrompt('You are a helpful assistant.')
            ->withMessages(collect($this->messages)->map->toPrism()->all())
            ->asStream();
        // ...
    }
// ...
```

Remember to add the corresponding API key to your `.env` file (e.g., `ANTHROPIC_API_KEY`).

### 2. Explore the Chat Interface

Navigate to your application's `/dashboard` route (or wherever you've set up the chat component) to start interacting with the chat interface.

## Core Components

### `app/Livewire/Chat.php`

This is the heart of the chat functionality.

### `resources/views/livewire/chat.blade.php`

This Blade view renders the chat interface.

## How it Works

1.  User types a message in `x-chat.message-input` and hits send.
2.  `sendMessage()` in `Chat.php` is triggered.
    - The user's message is added to the `$messages` array.
    - The input field is cleared.
    - `$this->js('$wire.runChatToolLoop()')` is called, which immediately invokes the `runChatToolLoop()` method. This allows the UI to update with the user's message before the LLM call.
3.  `runChatToolLoop()`:
    - Constructs a request to the LLM using Prism, including the system prompt and the current chat history.
    - The request is sent as a stream.
    - As tokens arrive from the LLM:
      - They are appended to a local `$message` variable.
      - The `stream()` method sends the accumulated `$message` (converted to markdown) to the frontend, updating the part of the view listening to `streamed-message`. This is typically handled by the `x-chat.assistant-message` component.
4.  Once the LLM finishes generating its response:
    - The complete assistant message is added to the `$messages` array. The temporary streamed display is effectively replaced by the final message in the loop.

## Contributing

Contributions are welcome! If you'd like to improve the Livewire Chat Kit, please feel free to:

- Report a bug.
- Suggest a new feature.
- Submit a pull request.

Please visit the [GitHub repository](https://github.com/kauffinger/livewire-chat-kit) to contribute.

## License

This project is open-sourced software licensed under the [MIT license](LICENSE.md).

```

```
