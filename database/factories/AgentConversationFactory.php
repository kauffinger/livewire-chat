<?php

namespace Database\Factories;

use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AgentConversation>
 */
class AgentConversationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid7(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
        ];
    }

    /**
     * Create a conversation with messages.
     */
    public function withMessages(int $count = 3): static
    {
        return $this->afterCreating(function (AgentConversation $conversation) use ($count): void {
            for ($i = 0; $i < $count; $i++) {
                AgentConversationMessage::create([
                    'id' => (string) Str::uuid7(),
                    'conversation_id' => $conversation->id,
                    'user_id' => $conversation->user_id,
                    'agent' => 'App\\Ai\\Agents\\ChatAgent',
                    'role' => $i % 2 === 0 ? 'user' : 'assistant',
                    'content' => fake()->paragraph(),
                    'attachments' => [],
                    'tool_calls' => [],
                    'tool_results' => [],
                    'usage' => [],
                    'meta' => [],
                    'created_at' => now()->addMinutes($i),
                    'updated_at' => now()->addMinutes($i),
                ]);
            }
        });
    }
}
