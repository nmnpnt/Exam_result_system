<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProgrammeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            'name' => $this->faker->words(3, true),
            'duration_years' => 4,
        ];
    }
}
