<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EnrollmentController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/enrollments",
     *     tags={"Enrollments"},
     *     summary="Enroll a student in an examination course",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="student_id", type="integer", example=1),
     *             @OA\Property(property="examination_course_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response="201", description="Enrollment created")
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'examination_course_id' => 'required|exists:examination_course,id',
        ]);

        try {
            $enrollment = Enrollment::create($data);
        } catch (\Illuminate\Database\QueryException $e) {
            // Unique constraint on (student_id, examination_course_id) turns a
            // duplicate-enroll attempt into a clean 409 instead of a 500.
            if ($e->getCode() === '23000') {
                throw ValidationException::withMessages([
                    'student_id' => 'Student is already enrolled for this examination course.',
                ]);
            }
            throw $e;
        }

        return response()->json($enrollment, 201);
    }
}
