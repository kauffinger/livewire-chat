@props(['message', 'isFirst' => false])

<div class="@if ($isFirst) flex-1 @endif flex w-full flex-row justify-start">
    <x-chat.message-content :raw-content="json_encode($message->parts)" />
</div>
