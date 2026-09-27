<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Examination;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExaminationCourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'examination_id' => Examination::factory(),
            'course_id' => Course::factory(),
            'max_marks' => 100,
            'pass_marks' => 40,
        ];
    }
}
