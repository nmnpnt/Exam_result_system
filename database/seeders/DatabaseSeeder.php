<?php

namespace Database\Seeders;

use App\Models\AssessmentComponent;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Examination;
use App\Models\ExaminationCourse;
use App\Models\Programme;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a small, realistic dataset for manual/API-client testing.
 * Creates an admin user with a known Sanctum token so the Postman
 * collection works out of the box.
 *
 * For load-testing the bulk-import path, generate a large CSV separately
 * (see README: "Load-testing the bulk import").
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin user (credentials: admin@exam.edu / password) ─────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@exam.edu'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );
        // Create a deterministic token so Postman collection works immediately.
        $token = $admin->createToken('demo-token');
        echo "\n  Admin token: {$token->plainTextToken}\n";
        echo "  (Use this as Bearer token, or POST /api/auth/login with admin@exam.edu / password)\n\n";

        // ── Programme ───────────────────────────────────────────────────
        $programme = Programme::create([
            'code' => 'CSE',
            'name' => 'B.Tech Computer Science',
            'duration_years' => 4,
        ]);

        $course = Course::create([
            'programme_id' => $programme->id,
            'code' => 'CS301',
            'name' => 'Database Systems',
            'credits' => 4,
        ]);

        $course2 = Course::create([
            'programme_id' => $programme->id,
            'code' => 'CS302',
            'name' => 'Operating Systems',
            'credits' => 3,
        ]);

        $examination = Examination::create([
            'programme_id' => $programme->id,
            'name' => 'Semester 5 End-Term',
            'academic_year' => '2025-2026',
            'term' => 'Semester 5',
            'status' => 'open',
        ]);

        $examinationCourse = ExaminationCourse::create([
            'examination_id' => $examination->id,
            'course_id' => $course->id,
            'max_marks' => 100,
            'pass_marks' => 40,
        ]);

        $examinationCourse2 = ExaminationCourse::create([
            'examination_id' => $examination->id,
            'course_id' => $course2->id,
            'max_marks' => 100,
            'pass_marks' => 40,
        ]);

        AssessmentComponent::insert([
            ['examination_course_id' => $examinationCourse->id, 'name' => 'Internal', 'max_marks' => 30, 'weight_percentage' => 30, 'created_at' => now(), 'updated_at' => now()],
            ['examination_course_id' => $examinationCourse->id, 'name' => 'Final', 'max_marks' => 70, 'weight_percentage' => 70, 'created_at' => now(), 'updated_at' => now()],
            ['examination_course_id' => $examinationCourse2->id, 'name' => 'Internal', 'max_marks' => 40, 'weight_percentage' => 40, 'created_at' => now(), 'updated_at' => now()],
            ['examination_course_id' => $examinationCourse2->id, 'name' => 'Final', 'max_marks' => 60, 'weight_percentage' => 60, 'created_at' => now(), 'updated_at' => now()],
        ]);

        collect(range(1, 20))->each(function (int $i) use ($programme, $examinationCourse, $examinationCourse2) {
            $student = Student::create([
                'programme_id' => $programme->id,
                'roll_number' => sprintf('CSE2026%03d', $i),
                'name' => "Student {$i}",
                'email' => "student{$i}@example.edu",
                'batch_year' => 2026,
            ]);

            Enrollment::create([
                'student_id' => $student->id,
                'examination_course_id' => $examinationCourse->id,
            ]);

            Enrollment::create([
                'student_id' => $student->id,
                'examination_course_id' => $examinationCourse2->id,
            ]);
        });

        echo "  Seeded: 1 programme, 2 courses, 1 examination, 2 exam-courses, 4 components, 20 students, 40 enrollments.\n";
    }
}
