<?php

namespace Database\Factories;

use App\Models\ExaminationCourse;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentComponentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'examination_course_id' => ExaminationCourse::factory(),
            'name' => 'Final',
            'max_marks' => 100,
            'weight_percentage' => 100,
        ];
    }
}
