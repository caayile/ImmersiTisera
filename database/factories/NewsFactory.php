<?php

namespace Database\Factories;

use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<News>
 */
class NewsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(6);

        return [
            'title' => $title,
            'slug' => News::makeSlug($title),
            'excerpt' => fake()->text(200),
            'body' => fake()->paragraphs(3, true),
            'cover_image' => null,
            'category' => fake()->randomElement(['pengumuman', 'kegiatan', 'prestasi', 'mitra']),
            'status' => 'published',
            'published_at' => now(),
            'author_id' => fake()->numberBetween(1, 3),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
}
