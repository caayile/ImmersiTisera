<?php

namespace Database\Factories;

use App\Models\Logbook;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Logbook>
 */
class LogbookFactory extends Factory
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

        return [
            'program_id' => $programId,
            'participant_id' => $participantId,
            'date' => fake()->dateTimeBetween('-30 days', now()),
            'activity' => fake()->sentence(),
            'attendance' => fake()->randomElement(['Hadir', 'Izin', 'Sakit', 'Tanpa Keterangan']),
            'what_i_did' => fake()->text(),
            'what_i_learned' => fake()->text(),
            'what_i_found' => fake()->text(),
            'value' => fake()->numberBetween(1, 5),
            'next_action' => fake()->sentence(),
            'attachment_path' => null,
            'mentor_feedback' => null,
            'status' => 'pending',
        ];
    }
}
