<?php

namespace Database\Factories;

use App\Models\Certificate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Certificate>
 */
class CertificateFactory extends Factory
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
            'number' => 'NO. '.str_pad(fake()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT).'/MD.TSU/B'.fake()->numberBetween(1, 10).'/'.['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][fake()->numberBetween(0, 11)].'/'.now()->year,
            'global_sequence' => fake()->numberBetween(1, 999),
            'batch' => (string) fake()->numberBetween(1, 10),
            'issued_month' => fake()->numberBetween(1, 12),
            'issued_year' => now()->year,
            'status' => 'draft',
            'mentor_signature' => null,
            'mentor_signature_path' => null,
            'snapshot' => null,
            'signatory_snapshot' => null,
            'issued_at' => null,
        ];
    }

    public function issued(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'issued',
            'issued_at' => now(),
        ]);
    }
}
