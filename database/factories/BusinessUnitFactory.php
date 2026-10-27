<?php

namespace Database\Factories;

use App\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessUnit>
 */
class BusinessUnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $departmentId = fake()->numberBetween(1, 10);

        return [
            'department_id' => $departmentId,
            'name' => fake()->company(),
            'description' => fake()->paragraph(),
            'function' => fake()->sentence(),
            'image_path' => null,
            'work_done' => fake()->text(),
            'example_activities' => fake()->text(),
            'requirements' => fake()->text(),
            'relevant_programs' => [],
            'period' => '2 bulan',
            'batch' => fake()->numberBetween(1, 5),
            'registration_deadline' => now()->addDays(fake()->numberBetween(30, 60)),
            'registration_start' => now()->subDays(fake()->numberBetween(0, 30)),
            'status' => fake()->randomElement(['open', 'closed']),
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
        ]);
    }
}
