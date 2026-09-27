<?php

namespace Tests\Feature;

use App\Models\AssessmentComponent;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Examination;
use App\Models\ExaminationCourse;
use App\Models\Programme;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class MarkUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_requires_idempotency_key(): void
    {
        $user = User::factory()->create();
        $examination = Examination::factory()->create(['status' => 'open']);

        $response = $this->actingAs($user)
            ->postJson("/api/examinations/{$examination->id}/marks/upload", [
                'file' => UploadedFile::fake()->create('marks.csv', 10),
            ]);

        $response->assertStatus(422);
    }

    public function test_duplicate_upload_with_same_idempotency_key_is_not_reprocessed(): void
    {
        Bus::fake();

        $user = User::factory()->create();
        $programme = Programme::factory()->create();
        $course = Course::factory()->create(['programme_id' => $programme->id]);
        $examination = Examination::factory()->create(['programme_id' => $programme->id, 'status' => 'open']);
        $examCourse = ExaminationCourse::factory()->create([
            'examination_id' => $examination->id,
            'course_id' => $course->id,
        ]);
        AssessmentComponent::factory()->create(['examination_course_id' => $examCourse->id, 'name' => 'Final', 'max_marks' => 100]);
        $student = Student::factory()->create(['programme_id' => $programme->id]);
        Enrollment::factory()->create(['student_id' => $student->id, 'examination_course_id' => $examCourse->id]);

        $csv = "roll_number,course_code,component_name,marks_obtained\n{$student->roll_number},{$course->code},Final,88\n";
        $file = UploadedFile::fake()->createWithContent('marks.csv', $csv);

        $headers = ['X-Idempotency-Key' => 'test-key-123'];

        $first = $this->actingAs($user)
            ->postJson("/api/examinations/{$examination->id}/marks/upload", ['file' => $file], $headers);
        $first->assertStatus(202);

        $file2 = UploadedFile::fake()->createWithContent('marks.csv', $csv);
        $second = $this->actingAs($user)
            ->postJson("/api/examinations/{$examination->id}/marks/upload", ['file' => $file2], $headers);

        // Same idempotency key + same body => original response replayed, not a fresh import.
        $second->assertStatus(202);
        $this->assertSame($first->json('batch_id'), $second->json('batch_id'));
        $this->assertDatabaseCount('mark_upload_batches', 1);
    }
}
