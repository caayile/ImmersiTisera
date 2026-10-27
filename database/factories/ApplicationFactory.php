<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\BusinessUnit;
use App\Models\Department;
use App\Models\Mentor;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'participant_id' => Participant::factory(),
            'department_id' => Department::factory(),
            'business_unit_id' => BusinessUnit::factory(),
            'batch' => 1,
            'mentor_id' => Mentor::factory(),
            'motivation' => fake()->paragraph(),
            'learning_objectives' => fake()->paragraph(),
            'planned_activities' => fake()->paragraph(),
            'expected_output' => fake()->sentence(),
            'campus_benefit' => fake()->paragraph(),
            'cv_path' => null,
            'shared_goal' => fake()->sentence(),
            'activity_types' => ['observasi'],
            'problem_statement' => fake()->paragraph(),
            'main_output' => fake()->sentence(),
            'participant_benefit' => fake()->paragraph(),
            'business_benefit' => fake()->paragraph(),
            'success_indicators' => [
                'Luaran program selesai dan divalidasi mentor',
                'Ada rencana tindak lanjut kolaborasi',
            ],
            'indicator_feedback' => null,
            'participant_signature' => null,
            'mentor_signature' => null,
            'participant_signed_at' => null,
            'mentor_signed_at' => null,
            'preferred_period' => '2 bulan',
            'period_start' => now()->toDateString(),
            'period_end' => now()->addMonths(2)->toDateString(),
            'match_score' => fake()->numberBetween(70, 95),
            'relevance_warning' => false,
            'matching_notes' => null,
            'letter_number' => null,
            'mentor_note' => null,
            'revision_note' => null,
            'status' => 'draft',
            'admin_reviewed_at' => null,
            'mentor_reviewed_at' => null,
            'admin_finalized_at' => null,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'submitted',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }
}
