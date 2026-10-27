<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word().' '.fake()->word();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'subtitle' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'function' => fake()->sentence(),
            'area' => 'Solo, Indonesia',
            'map_url' => 'https://maps.google.com/maps?q=Solo,Indonesia&hl=id&z=15&output=embed',
            'image_path' => null,
            'status' => 'active',
        ];
    }
}
