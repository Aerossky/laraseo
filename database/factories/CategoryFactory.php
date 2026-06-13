<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        // Slug is generated automatically by the HasSlug trait on save.
        return [
            'name' => Str::title(fake()->unique()->words(2, true)),
            'description' => fake()->sentence(),
        ];
    }
}
