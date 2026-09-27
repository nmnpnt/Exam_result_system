<?php

namespace Database\Factories;

use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'programme_id' => Programme::factory(),
            'code' => strtoupper($this->faker->unique()->bothify('??###')),
            'name' => $this->faker->sentence(3),
            'credits' => $this->faker->numberBetween(2, 5),
        ];
    }
}
