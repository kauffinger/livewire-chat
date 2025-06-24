<?php

namespace App\Actions;

use App\Models\Chat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AddNewUserMessageToChat
{
    public function handle(Chat $chat, string $userMessage): void
    {
        DB::transaction(function () use ($chat, $userMessage): void {
            $chat->messages()->create([
                'role' => 'user',
                'parts' => [
                    'text' => $userMessage,
                ],
                'attachments' => '[]',
            ]);

            $this->updateChatTitleIfMessageIsFirstMessage($chat, $userMessage);
        });
    }

    private function updateChatTitleIfMessageIsFirstMessage(Chat $chat, string $userMessage): void
    {
        if ($chat->messages()->count() === 1) {
            $chat->update([
                'title' => Str::limit($userMessage, 50),
            ]);
        }
    }
}
