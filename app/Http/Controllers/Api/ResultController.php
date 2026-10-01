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
    /**
     * @OA\Get(
     *     path="/api/results",
     *     tags={"Results"},
     *     summary="Get all computed results",
     *     @OA\Response(response="200", description="A flat list of student results used by the Dashboard UI")
     * )
     */
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
     * @OA\Post(
     *     path="/api/examinations/{examination}/results/compute",
     *     tags={"Results"},
     *     summary="Compute all grades for an examination",
     *     description="Dispatches a fan-out queue architecture to compute grades for every student asynchronously.",
     *     @OA\Parameter(name="examination", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="202", description="Computation jobs dispatched to the queue")
     * )
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
     * @OA\Post(
     *     path="/api/examinations/{examination}/results/publish",
     *     tags={"Results"},
     *     summary="Publish computed results for an examination",
     *     description="Moves all computed results to 'published' status and makes them visible to students. Requires X-Idempotency-Key header.",
     *     @OA\Parameter(name="examination", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Results published with count")
     * )
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

    /**
     * @OA\Get(
     *     path="/api/enrollments/{enrollment}/result",
     *     tags={"Results"},
     *     summary="View a single published result for an enrollment",
     *     @OA\Parameter(name="enrollment", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Published result"),
     *     @OA\Response(response="404", description="Result not available or not yet published")
     * )
     */
    public function show(Enrollment $enrollment): JsonResponse
    {
        $result = $enrollment->result;

        if (! $result || $result->status !== 'published') {
            return response()->json(['message' => 'Result not available.'], 404);
        }

        return response()->json($result);
    }
}
