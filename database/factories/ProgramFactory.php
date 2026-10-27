<?php

namespace Database\Factories;

use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'application_id' => null,
            'participant_id' => null,
            'mentor_id' => null,
            'department_id' => null,
            'business_unit_id' => null,
            'start_date' => now()->addDays(fake()->numberBetween(0, 30)),
            'end_date' => now()->addDays(fake()->numberBetween(30, 90)),
            'progress' => 0,
            'current_week' => 1,
            'status' => 'draft',
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'current_week' => fake()->numberBetween(1, 8),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'progress' => 100,
        ]);
    }
}