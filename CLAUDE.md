# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is **Livewire Chat Kit** - a Laravel Starter Kit for building LLM-powered chat interfaces. It combines Laravel 12, Livewire 3, FluxUI components, and Prism PHP for LLM integration to create chat applications with minimal JavaScript.

## Development Commands

```bash
# Laravel Herd handles server/queue/logs automatically
# Only run this if you need console output visibility
composer dev

# Run tests
composer test

# Format code (PHP + JS/CSS)
composer format

# Check formatting
composer format:check

# Build assets for development
npm run dev

# Build assets for production
npm run build
```

## Architecture Overview

### Core Chat System

- **`app/Livewire/Chat.php`** - Main chat component handling LLM streaming and message management
- **`app/Livewire/Chat/Sidebar.php`** - Chat navigation and history
- **Message flow**: User input → `sendMessage()` → `runChatToolLoop()` → Prism LLM streaming → real-time UI updates via Livewire streams

### Data Layer

- **Models**: `User`, `Chat`, `Message` with proper relationships
- **DTOs**: `UserMessage`, `AssistantMessage` for type-safe message handling
- **Enums**: `OpenAiModel`, `Visibility` for consistent value objects
- **Policies**: `ChatPolicy` for authorization

### Frontend Architecture

- **Livewire-first**: Dynamic UI with minimal JavaScript
- **FluxUI components**: Professional UI component library (find available components by googling "site:fluxui.dev <component name>"). Only use free components.
- **Streaming responses**: Real-time message updates using Livewire streams
- **Markdown rendering**: Built-in markdown-it with syntax highlighting

### LLM Integration (Prism)

- **Default provider**: OpenAI GPT-4o-mini
- **Multi-provider support**: Easy switching between OpenAI, Anthropic, etc.
- **Streaming**: Real-time token streaming for smooth UX
- **Configuration**: LLM provider/model configurable per chat

## Key Technical Patterns

### Message Streaming Flow

1. User submits message via `x-chat.message-input`
2. `sendMessage()` adds user message to array, clears input
3. `$this->js('$wire.runChatToolLoop()')` triggers LLM call
4. `runChatToolLoop()` streams tokens via `$this->stream()`
5. Frontend `x-chat.assistant-message` updates in real-time
6. Complete message added to permanent `$messages` array

### Environment Configuration

- **Required**: `OPENAI_API_KEY` (or provider-specific keys)
- **Database**: SQLite by default, configurable
- **Development**: Laravel Herd handles server/queue/logs automatically

### File Structure Patterns

- **`/app/Livewire`** - All Livewire components
- **`/resources/views/livewire`** - Component Blade templates
- **`/resources/js`** - Minimal JavaScript (streaming markdown component)
- **`/tests`** - Pest tests with Livewire testing utilities

## Development Notes

### Code Style

- **PHP**: Laravel Pint for formatting, Rector for refactoring
- **JavaScript/CSS**: Prettier with Tailwind plugin
- **Blade**: Prettier blade plugin for template formatting

### Testing

- **Framework**: Pest PHP with Livewire plugin
- **Coverage**: Feature and Unit tests for core functionality

### Provider Switching

To switch LLM providers, modify the `runChatToolLoop()` method in `Chat.php`:

```php
$generator = Prism::text()
    ->using(Provider::Anthropic, 'claude-3-sonnet-20240229')
    // ->using(Provider::OpenAI, 'gpt-4o-mini') // Default
```

### Asset Pipeline

- **Vite**: Modern build tool with hot reload
- **TailwindCSS 4**: Utility-first CSS with typography plugin
- **Laravel Herd**: Development environment handles all services automatically

## Development Workflow Notes

- You don't need to run npm run build, i have npm run dev running
