<?php

namespace Database\Factories;

use App\Models\Opportunity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Opportunity>
 */
class OpportunityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mentorId = fake()->numberBetween(1, 3);

        return [
            'mentor_id' => $mentorId,
            'title' => fake()->sentence(4),
            'field' => fake()->randomElement(['Teknik', 'Ekonomi', 'Kesehatan', 'Pendidikan', 'Sosial']),
            'business_unit' => fake()->company(),
            'purpose' => fake()->paragraph(),
            'problem' => fake()->paragraph(),
            'opportunity' => fake()->paragraph(),
            'needed_expertise' => [],
            'expected_output' => fake()->paragraph(),
            'allowed_activities' => [],
            'timeline_start' => now()->addDays(fake()->numberBetween(0, 30)),
            'timeline_end' => now()->addMonths(2),
            'status' => 'open',
        ];
    }
}
