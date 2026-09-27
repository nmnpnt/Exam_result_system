<?php

namespace Database\Factories;

use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'programme_id' => Programme::factory(),
            'roll_number' => strtoupper($this->faker->unique()->bothify('??####??')),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'batch_year' => 2026,
        ];
    }
}
