<?php

namespace Database\Factories;

use App\Models\ExaminationCourse;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'examination_course_id' => ExaminationCourse::factory(),
            'status' => 'registered',
        ];
    }
}
