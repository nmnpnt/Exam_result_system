<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\CalculateExaminationResults;
use App\Models\Enrollment;
use App\Models\Examination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    /** GET /api/results */
    public function index(): JsonResponse
    {
        $results = \App\Models\Result::with(['enrollment.student.programme', 'enrollment.examinationCourse.course'])->get()->map(function($result) {
            return [
                'id' => $result->id,
                'total_marks' => $result->total_marks_obtained,
                'grade' => $result->grade,
                'status' => $result->status,
                'student' => [
                    'name' => $result->enrollment->student->name,
                    'roll_number' => $result->enrollment->student->roll_number,
                    'batch_year' => $result->enrollment->student->batch_year,
                    'programme' => [
                        'code' => $result->enrollment->student->programme->code ?? 'N/A'
                    ]
                ],
                'course' => [
                    'code' => $result->enrollment->examinationCourse->course->code ?? 'N/A',
                    'name' => $result->enrollment->examinationCourse->course->name ?? 'N/A'
                ]
            ];
        });
        
        return response()->json(['data' => $results]);
    }

    /**
     * POST /api/examinations/{examination}/results/compute
     * Fans out result computation across the whole examination asynchronously.
     */
    public function compute(Examination $examination): JsonResponse
    {
        if (! in_array($examination->status, ['locked', 'open'], true)) {
            return response()->json([
                'message' => "Cannot compute results while examination status is '{$examination->status}'.",
            ], 422);
        }

        CalculateExaminationResults::dispatch($examination->id);

        return response()->json([
            'message' => 'Result computation started.',
            'examination_id' => $examination->id,
        ], 202);
    }

    /**
     * POST /api/examinations/{examination}/results/publish
     * Publishing is a deliberate, separate step from computing — a result
     * can be reviewed while `computed` before being made visible.
     */
    public function publish(Examination $examination): JsonResponse
    {
        $updated = $examination->examinationCourses()
            ->with('enrollments.result')
            ->get()
            ->flatMap(fn ($ec) => $ec->enrollments)
            ->pluck('result')
            ->filter(fn ($result) => $result && $result->status === 'computed')
            ->each(fn ($result) => $result->update(['status' => 'published', 'published_at' => now()]));

        $examination->update(['status' => 'published', 'published_at' => now()]);

        return response()->json([
            'message' => 'Results published.',
            'published_count' => $updated->count(),
        ]);
    }

    /** GET /api/enrollments/{enrollment}/result */
    public function show(Enrollment $enrollment): JsonResponse
    {
        $result = $enrollment->result;

        if (! $result || $result->status !== 'published') {
            return response()->json(['message' => 'Result not available.'], 404);
        }

        return response()->json($result);
    }
}
