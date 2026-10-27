<?php

namespace Database\Factories;

use App\Models\BusinessUnit;
use App\Models\Department;
use App\Models\Mentor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mentor>
 */
class MentorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(['role' => 'mentor']),
            'nik' => fake()->numerify('##############'),
            'department_id' => Department::factory(),
            'business_unit_id' => BusinessUnit::factory(),
            'position' => 'Mentor',
            'expertise' => [],
            'certificate_signature' => null,
        ];
    }
}
