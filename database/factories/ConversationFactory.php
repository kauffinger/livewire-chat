<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Laravel\Ai\Models\Conversation;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Conversation>
     */
    protected $model = Conversation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid7(),
            'participant_type' => (new User)->getMorphClass(),
            'participant_id' => User::factory(),
            'title' => fake()->sentence(3),
        ];
    }

    /**
     * Create a conversation with alternating user and assistant messages.
     */
    public function withMessages(int $count = 3): static
    {
        return $this->has(
            ConversationMessageFactory::new()
                ->count($count)
                ->sequence(['role' => 'user'], ['role' => 'assistant']),
            'messages',
        );
    }
}
