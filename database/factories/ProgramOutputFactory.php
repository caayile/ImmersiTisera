<?php

namespace Database\Factories;

use App\Models\ProgramOutput;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgramOutput>
 */
class ProgramOutputFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $programId = fake()->numberBetween(1, 3);
        $participantId = fake()->numberBetween(1, 5);
        $departmentId = fake()->numberBetween(1, 10);
        $businessUnitId = fake()->numberBetween(1, 20);

        return [
            'program_id' => $programId,
            'participant_id' => $participantId,
            'title' => fake()->sentence(),
            'type' => fake()->randomElement(['dokumen', 'link', 'file']),
            'description' => fake()->text(),
            'file_path' => null,
            'link' => null,
            'hasil_file_path' => null,
            'laporan_link' => null,
            'department_id' => $departmentId,
            'business_unit_id' => $businessUnitId,
            'year' => now()->year,
            'is_main_output' => false,
            'is_final_report' => false,
            'mentor_feedback' => null,
            'status' => 'pending',
        ];
    }
}
