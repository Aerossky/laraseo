<?php

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        // Slug is generated automatically by the HasSlug trait on save.
        return [
            'category_id' => Category::factory(),
            'title' => Str::title(fake()->unique()->sentence(4)),
            'excerpt' => fake()->sentence(),
            'content' => [
                'time' => now()->timestamp,
                'blocks' => [],
                'version' => '2.31.0',
            ],
            'status' => PostStatus::Draft,
            'show_toc' => true,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => PostStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn () => [
            'status' => PostStatus::Scheduled,
            'published_at' => now()->addWeek(),
        ]);
    }
}
