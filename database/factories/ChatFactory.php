<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chat>
 */
final class ChatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(5),
            'visibility' => $this->faker->randomElement(['public', 'private']),
            'model' => 'gpt-4o-mini',
        ];
    }

    /**
     * Add messages to the chat.
     */
    public function withMessages(int $count = 5): self
    {
        return $this->has(Message::factory()->count($count));
    }
}
