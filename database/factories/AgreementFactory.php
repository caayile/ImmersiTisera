<?php

namespace Database\Factories;

use App\Models\Agreement;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Agreement>
 */
class AgreementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'program_id' => Program::factory(),
            'objective' => fake()->sentence(),
            'problem_statement' => fake()->paragraph(),
            'activities' => fake()->sentence(),
            'main_output' => fake()->sentence(),
            'participant_benefit' => fake()->paragraph(),
            'business_benefit' => fake()->paragraph(),
            'success_indicators' => [],
            'collaboration_potential' => fake()->sentence(),
            'revision_note' => null,
            'status' => 'pending',
            'letter_number' => null,
            'letter_issued_at' => null,
            'participant_approved_at' => null,
            'mentor_approved_at' => null,
            'participant_signature' => null,
            'mentor_signature' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'agreed',
            'participant_approved_at' => now(),
            'letter_number' => '001/MD.TSU/B1/I/2026',
        ]);
    }
}
