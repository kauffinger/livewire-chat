<?php

namespace Database\Factories;

use App\Ai\Agents\ChatAgent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Laravel\Ai\Enums\MessageStatus;
use Laravel\Ai\Models\ConversationMessage;

/**
 * @extends Factory<ConversationMessage>
 */
class ConversationMessageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<ConversationMessage>
     */
    protected $model = ConversationMessage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid7(),
            'conversation_id' => ConversationFactory::new(),
            'agent' => ChatAgent::class,
            'role' => 'user',
            'content' => fake()->paragraph(),
            'attachments' => [],
            'steps' => [],
            'usage' => [],
            'meta' => [],
            'status' => MessageStatus::Completed,
        ];
    }

    /**
     * Indicate that the message was written by the assistant.
     */
    public function assistant(): static
    {
        return $this->state(['role' => 'assistant']);
    }
}
