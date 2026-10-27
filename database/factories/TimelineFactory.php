<?php

namespace Database\Factories;

use App\Models\Timeline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Timeline>
 */
class TimelineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $programId = fake()->numberBetween(1, 3);
        $week = fake()->numberBetween(1, 8);

        return [
            'program_id' => $programId,
            'week' => $week,
            'phase' => fake()->randomElement(['Identify', 'Immersion', 'Interaction', 'Impact', 'Integration']),
            'title' => fake()->sentence(),
            'description' => fake()->text(),
            'expected_output' => fake()->sentence(),
            'evidence_link' => null,
            'attachment_path' => null,
            'mentor_note' => null,
            'status' => 'pending',
        ];
    }
}
