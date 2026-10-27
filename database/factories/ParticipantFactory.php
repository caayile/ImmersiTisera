<?php

namespace Database\Factories;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Participant>
 */
class ParticipantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(['role' => 'user']),
            'nidn' => fake()->numerify('##########'),
            'faculty' => fake()->randomElement(['Fakultas Teknik', 'Fakultas Ekonomi', 'Fakultas Sains']),
            'study_program' => 'Teknik Informatika',
            'expertise' => [],
            'competency' => [],
            'experience' => fake()->sentence(),
            'motivation' => fake()->paragraph(),
            'profile_data' => [],
        ];
    }
}
