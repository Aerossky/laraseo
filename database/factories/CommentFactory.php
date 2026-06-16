<?php

namespace Database\Factories;

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'user_id' => null,
            'author_name' => $this->faker->name(),
            'author_email' => $this->faker->safeEmail(),
            'body' => $this->faker->paragraph(),
            'status' => CommentStatus::Pending,
            'ip_address' => $this->faker->ipv4(),
            'approved_at' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (): array => [
            'status' => CommentStatus::Approved,
            'approved_at' => now(),
        ]);
    }

    public function spam(): static
    {
        return $this->state(fn (): array => [
            'status' => CommentStatus::Spam,
        ]);
    }
}
