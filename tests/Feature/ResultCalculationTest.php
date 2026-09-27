<?php

namespace Tests\Feature;

use App\Models\AssessmentComponent;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Examination;
use App\Models\ExaminationCourse;
use App\Models\Mark;
use App\Models\Programme;
use App\Models\Student;
use App\Services\ResultCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_weighted_percentage_and_grade_are_computed_correctly(): void
    {
        $programme = Programme::factory()->create();
        $course = Course::factory()->create(['programme_id' => $programme->id]);
        $examination = Examination::factory()->create(['programme_id' => $programme->id]);
        $examCourse = ExaminationCourse::factory()->create([
            'examination_id' => $examination->id,
            'course_id' => $course->id,
            'max_marks' => 100,
            'pass_marks' => 40,
        ]);
        $internal = AssessmentComponent::factory()->create([
            'examination_course_id' => $examCourse->id, 'name' => 'Internal', 'max_marks' => 30, 'weight_percentage' => 30,
        ]);
        $final = AssessmentComponent::factory()->create([
            'examination_course_id' => $examCourse->id, 'name' => 'Final', 'max_marks' => 70, 'weight_percentage' => 70,
        ]);

        $student = Student::factory()->create(['programme_id' => $programme->id]);
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id, 'examination_course_id' => $examCourse->id,
        ]);

        Mark::create(['enrollment_id' => $enrollment->id, 'assessment_component_id' => $internal->id, 'marks_obtained' => 24, 'status' => 'valid']);
        Mark::create(['enrollment_id' => $enrollment->id, 'assessment_component_id' => $final->id, 'marks_obtained' => 56, 'status' => 'valid']);

        $result = (new ResultCalculationService)->calculate($enrollment->fresh(['examinationCourse.assessmentComponents', 'marks']));

        $this->assertEquals(80.0, $result->percentage);
        $this->assertEquals('A', $result->grade);
        $this->assertEquals('pass', $result->outcome);
    }

    public function test_result_is_pending_when_a_component_is_missing(): void
    {
        $programme = Programme::factory()->create();
        $course = Course::factory()->create(['programme_id' => $programme->id]);
        $examination = Examination::factory()->create(['programme_id' => $programme->id]);
        $examCourse = ExaminationCourse::factory()->create([
            'examination_id' => $examination->id, 'course_id' => $course->id,
        ]);
        AssessmentComponent::factory()->create(['examination_course_id' => $examCourse->id, 'name' => 'Internal', 'max_marks' => 30]);
        AssessmentComponent::factory()->create(['examination_course_id' => $examCourse->id, 'name' => 'Final', 'max_marks' => 70]);

        $student = Student::factory()->create(['programme_id' => $programme->id]);
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id, 'examination_course_id' => $examCourse->id,
        ]);

        $result = (new ResultCalculationService)->calculate($enrollment->fresh(['examinationCourse.assessmentComponents', 'marks']));

        $this->assertEquals('incomplete', $result->outcome);
        $this->assertEquals('pending', $result->status);
    }
}
