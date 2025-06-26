<?php

namespace App\Actions;

use App\Dtos\StreamData;
use App\Models\Chat;
use Illuminate\Support\Facades\DB;

class PersistStreamDataToMessages
{
    public function handle(Chat $chat, StreamData $streamData): void
    {
        DB::transaction(function () use ($chat, $streamData): void {
            if ($streamData->toolResults !== []) {
                $chat->messages()->create([
                    'role' => 'tool_result',
                    'parts' => ['toolResults' => $streamData->toolResults],
                    'attachments' => '[]',
                ]);
            }

            $chat->messages()->create([
                'role' => 'assistant',
                'parts' => $streamData->toArray(),
                'attachments' => '[]',
            ]);

            $chat->touch();
        });
    }
}
