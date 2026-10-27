<?php

namespace Database\Factories;

use App\Models\Evaluation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Evaluation>
 */
class EvaluationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $programId = fake()->numberBetween(1, 3);
        $evaluatorId = fake()->numberBetween(1, 5);

        return [
            'program_id' => $programId,
            'evaluator_id' => $evaluatorId,
            'industry_understanding' => fake()->numberBetween(1, 5),
            'relationship' => fake()->numberBetween(1, 5),
            'output' => fake()->numberBetween(1, 5),
            'mutual_benefit' => fake()->numberBetween(1, 5),
            'collaboration_potential' => fake()->numberBetween(1, 5),
            'criteria' => [],
            'grade_groups' => null,
            'comments' => fake()->text(),
        ];
    }

    public function withReport(): static
    {
        return $this->state(fn (array $attributes) => [
            'grade_groups' => Evaluation::defaultGradeGroups(),
        ]);
    }
}
