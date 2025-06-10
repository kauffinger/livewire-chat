<x-layouts.app :title="$chat->title ?? __('Chat')">
    <livewire:chat :chat="$chat" :wire:key="$chat->id" />
</x-layouts.app>
