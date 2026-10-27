<?php

namespace Database\Factories;

use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $programId = fake()->numberBetween(1, 3);

        return [
            'program_id' => $programId,
            'content' => [
                'executive_summary' => fake()->text(),
                'problem_background' => fake()->text(),
                'methodology' => fake()->text(),
                'results' => fake()->text(),
                'conclusion' => fake()->text(),
                'recommendations' => fake()->text(),
            ],
            'status' => 'draft',
        ];
    }
}
