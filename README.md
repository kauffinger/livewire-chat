# Livewire Chat Kit

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kauffinger/livewire-chat-kit.svg?style=flat-square)](https://packagist.org/packages/kauffinger/livewire-chat-kit)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/kauffinger/livewire-chat/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/kauffinger/livewire-chat/actions?query=workflow%3Atests+branch%3Amain)
[![Linter Action Status](https://img.shields.io/github/actions/workflow/status/kauffinger/livewire-chat/lint.yml?branch=main&label=linter&style=flat-square)](https://github.com/kauffinger/livewire-chat/actions?query=workflow%3Alint+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/kauffinger/livewire-chat-kit.svg?style=flat-square)](https://packagist.org/packages/kauffinger/livewire-chat-kit)

A Laravel Starter Kit for building LLM-powered chat interfaces with Livewire, [FluxUI](https://fluxui.com), and the [Laravel AI SDK](https://laravel.com/docs/ai-sdk). Jump right into the LLM party without touching (much) JavaScript, while still having a good-looking and effortlessly feeling chat interface.

This starter kit provides a clean, simple foundation for creating chat applications. It's designed to get you sending your first chat messages to an LLM instantly.

## Features

### Chat System

- **Multi-Conversation Management:** Create, manage, and navigate between multiple conversations
- **Chat Sidebar:** Quick access to your most recent conversations
- **Persistent History:** Conversations are stored by the AI SDK and accessible across sessions

### LLM Integration

- **Livewire-Powered:** Build dynamic interfaces with PHP.
- **Streamed Responses:** Real-time message streaming from LLMs for a smooth UX.
- **Laravel AI SDK:** The first-party way of speaking to LLMs. Easy to use, test, and switch between providers (e.g., OpenAI, Anthropic).
- **Tool Support:** Built-in LLM tool calling with visual feedback

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

This starter kit uses the [Laravel AI SDK](https://laravel.com/docs/ai-sdk) to interact with LLMs. By default, it's configured to use OpenAI. You'll need to add your API key to your `.env` file.

```env
OPENAI_API_KEY=your-openai-api-key
```

The default provider is set in `config/ai.php`. You can switch the chat to another provider or model with attributes on the agent. For example, to use Claude:

```php
// In app/Ai/Agents/ChatAgent.php
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Enums\Lab;

#[Provider(Lab::Anthropic)]
#[Model('claude-sonnet-5')]
class ChatAgent implements Agent, Conversational, HasTools
{
    // ...
}
```

Remember to add the corresponding API key to your `.env` file (e.g., `ANTHROPIC_API_KEY`).

To change the default provider without touching the agent, set `AI_PROVIDER` to any provider in `config/ai.php`. This is also how you point the chat at an OpenAI-compatible gateway or a local model server:

```env
AI_PROVIDER=openai-compatible
OPENAI_COMPATIBLE_URL=https://your-gateway.example/v1
OPENAI_COMPATIBLE_API_KEY=your-gateway-key
OPENAI_COMPATIBLE_MODEL=your-model
```

### 2. Explore the Chat Interface

Navigate to your application's `/dashboard` route to start interacting with the chat interface. You can:

- Create new conversations from the sidebar
- Switch between existing conversations
- Use built-in tools (like the example sum calculator)

## Architecture

### Core Components

- **`app/Ai/Agents/ChatAgent.php`** - The agent: instructions, tools, and conversation memory
- **`app/Ai/Tools/Sum.php`** - Example tool the agent can call
- **`app/Livewire/Chats/Index.php`** - Paginate over all of your conversations
- **`app/Livewire/Chats/Show.php`** - Main chat component handling LLM streaming and message display
- **`app/Livewire/ChatSidebar.php`** - Conversation navigation, history, and new conversation creation
- **`app/Actions/UpdateStreamDataFromAiEvent.php`** - Folds the AI SDK's stream events into the data the UI renders

Conversations and messages are stored by the AI SDK. The kit uses the SDK's own `Laravel\Ai\Models\Conversation` and `Laravel\Ai\Models\ConversationMessage` models, and the `User` model uses the SDK's `HasConversations` trait.

### UI Components

- **Chat Interface:** Real-time streaming with markdown rendering
- **Tool Calls Display:** Visual feedback for LLM tool executions
- **Toast Notifications:** Built-in toast system for user feedback

## How it Works

### Message Flow

1. **User Input:** Message typed in chat interface and submitted
2. **Pending Message:** `sendMessage()` shows the message right away and kicks off `runAgent()`
3. **LLM Streaming:** `runAgent()` continues the conversation with `ChatAgent` and streams the response
4. **Stream Handling:** `UpdateStreamDataFromAiEvent` processes each event (text, reasoning, tool calls, results)
5. **UI Updates:** Livewire streams update the interface in real-time
6. **Persistence:** The AI SDK stores the user message and the assistant's turn when the response completes

### Tool Integration

- **Tool Definitions:** Tools live in `app/Ai/Tools` and are registered in `ChatAgent::tools()`. Create new ones with `php artisan make:tool`
- **Automatic Execution:** LLM can call tools during conversation flow
- **Visual Feedback:** Tool calls are displayed while streaming and in the conversation history

### Technical Implementation

- **Ownership Checks:** Conversations can only be opened by the user they belong to
- **Testing:** Comprehensive Pest test suite with Livewire integration and faked agents
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
