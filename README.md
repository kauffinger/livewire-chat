# Livewire Chat Kit

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kauffinger/livewire-chat-kit.svg?style=flat-square)](https://packagist.org/packages/kauffinger/livewire-chat-kit)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/kauffinger/livewire-chat/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/kauffinger/livewire-chat/actions?query=workflow%3Atests+branch%3Amain)
[![Linter Action Status](https://img.shields.io/github/actions/workflow/status/kauffinger/livewire-chat/lint.yml?branch=main&label=linter&style=flat-square)](https://github.com/kauffinger/livewire-chat/actions?query=workflow%3Alint+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/kauffinger/livewire-chat-kit.svg?style=flat-square)](https://packagist.org/packages/kauffinger/livewire-chat-kit)

A Laravel Starter Kit for building LLM-powered chat interfaces with Livewire, [FluxUI](https://fluxui.com), and [Prism](https://github.com/prism-php/prism). Jump right into the LLM party without touching (much) JavaScript, while still having a good-looking and effortlessly feeling chat interface.

This starter kit provides a clean, simple foundation for creating chat applications. It's designed to get you sending your first chat messages to an LLM instantly.

## Features

### Chat System

- **Multi-Chat Management:** Create, manage, and navigate between multiple chat conversations
- **Chat Sidebar:** Quick access to recent chats with intelligent navigation
- **Chat Sharing:** Share conversations publicly or keep them private with visibility controls
- **Model Selection:** Choose from different LLM models (GPT-4o, GPT-4o-mini, etc.) per chat
- **Persistent History:** All conversations are saved and accessible across sessions

### LLM Integration

- **Livewire-Powered:** Build dynamic interfaces with PHP.
- **Streamed Responses:** Real-time message streaming from LLMs for a smooth UX.
- **Prism Integration:** The Laravel-way of speaking to LLMs. Easy to use, test, and switch between providers (e.g., OpenAI, Anthropic).
- **Tool Support:** Built-in LLM tool calling with visual feedback and result display

### UI & Design

- **FluxUI Components:** Beautiful, pre-built UI components for a polished look and feel.
- **Minimal JavaScript:** Focus on your PHP backend.
- **TailwindCSS Styled:** Includes a TailwindCSS setup with a typography plugin for rendering markdown.
- **Real-time Updates:** Seamless UI updates using Livewire streams

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

This starter kit uses [Prism](https://github.com/prism-php/prism) to interact with LLMs. By default, it's configured to use OpenAI's `gpt-4o-mini`. You'll need to add your API key to your `.env` file.

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

Navigate to your application's `/dashboard` route to start interacting with the chat interface. You can:

- Create new chats from the sidebar
- Switch between existing conversations
- Share chats publicly or keep them private
- Change the LLM model for each chat
- Use built-in tools (like the example sum calculator)

## Architecture

### Core Components

- **`app/Livewire/Chats/Index.php`** - Paginate over all of your chats
- **`app/Livewire/Chats/Show.php`** - Main chat component handling LLM streaming, tool calls, and message management
- **`app/Livewire/ChatSidebar.php`** - Chat navigation, history, and new chat creation
- **`app/Models/Chat.php`** - Chat model with user relationships and visibility controls
- **`app/Models/Message.php`** - Message model with tool call/result support and Prism integration

### Business Logic Layer

- **`app/Actions/`** - Clean action classes for complex operations:
  - `AddNewUserMessageToChat` - Handles user message persistence
  - `UpdateStreamDataFromPrismChunk` - Processes streaming LLM responses
  - `PersistStreamDataToMessages` - Saves complete responses to database
- **`app/Dtos/StreamData.php`** - Type-safe data transfer object for streaming
- **`app/Policies/ChatPolicy.php`** - Authorization rules for chat access and sharing

### UI Components

- **Chat Interface:** Real-time streaming with markdown rendering
- **Tool Results Display:** Visual feedback for LLM tool executions
- **Model Selector:** Per-chat model configuration
- **Sharing Controls:** Public/private visibility management
- **Toast Notifications:** Built-in toast system for user feedback

## How it Works

### Message Flow

1. **User Input:** Message typed in chat interface and submitted
2. **Message Processing:** `AddNewUserMessageToChat` action persists user message
3. **LLM Streaming:** `runChatToolLoop()` initiates real-time streaming with Prism
4. **Stream Handling:** `UpdateStreamDataFromPrismChunk` processes each chunk (text, tool calls, results)
5. **UI Updates:** Livewire streams update the interface in real-time
6. **Persistence:** `PersistStreamDataToMessages` saves complete conversation

### Tool Integration

- **Tool Definitions:** Tools are defined in the `runChatToolLoop()` method
- **Automatic Execution:** LLM can call tools during conversation flow
- **Visual Feedback:** Tool calls and results are displayed with dedicated UI components

### Technical Implementation

- **UUID Models:** All models use UUIDs for security and scalability
- **Policy Authorization:** Fine-grained access control with `ChatPolicy`
- **Type Safety:** DTOs and strong typing throughout the application
- **Testing:** Comprehensive Pest test suite with Livewire integration
- **Code Quality:** Laravel Pint, Rector, and Prettier for consistent formatting

## Toast Notifications

The chat kit includes a built-in toast notification system for user feedback.

### Basic Setup

Add the toast container to your layout (e.g., in `resources/views/layouts/app.blade.php`):

```blade
<x-toast position="bottom-right" />
```

### Usage in Livewire Components

```php
// In your Livewire component - using named parameters
$this->dispatch('toast',
    variant: 'success',
    title: 'Success!',
    description: 'Your changes have been saved.',
    icon: 'check-circle',
    duration: 3000
);
```

### Usage with Alpine.js

```blade
<button
  @click="$dispatch('toast', {
    variant: 'success',
    title: 'Success!',
    description: 'Your changes have been saved.'
})"
>
  Show Success Toast
</button>
```

### Toast Options

- `variant`: 'default' | 'success' | 'error' | 'danger' | 'warning' | 'info'
- `title`: The main message
- `description`: Additional details (optional)
- `icon`: Icon name (optional, auto-set based on variant)
- `dismissible`: Whether the toast can be dismissed (default: true)
- `duration`: Auto-dismiss after milliseconds (default: 5000, use 0 to disable)

### Container Positions

- `position`: 'top-left' | 'top-center' | 'top-right' | 'bottom-left' | 'bottom-center' | 'bottom-right' (default: 'bottom-right')

## Contributing

Contributions are welcome! If you'd like to improve the Livewire Chat Kit, please feel free to:

- Report a bug.
- Suggest a new feature.
- Submit a pull request.

Please visit the [GitHub repository](https://github.com/kauffinger/livewire-chat-kit) to contribute.

## License

This project is open-sourced software licensed under the [MIT license](LICENSE.md).
