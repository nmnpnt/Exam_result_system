<?php

namespace Database\Factories;

use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExaminationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'programme_id' => Programme::factory(),
            'name' => 'End-Term '.$this->faker->year(),
            'academic_year' => '2025-2026',
            'term' => 'Semester '.$this->faker->numberBetween(1, 8),
            'status' => 'draft',
        ];
    }
}
