<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMarkRequest;
use App\Jobs\CalculateStudentResult;
use App\Models\Mark;
use App\Services\MarksValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class MarkController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/marks",
     *     tags={"Marks"},
     *     summary="Submit or correct a single mark",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="roll_number", type="string", example="CSE2026001"),
     *             @OA\Property(property="course_code", type="string", example="CS301"),
     *             @OA\Property(property="component_name", type="string", example="Internal"),
     *             @OA\Property(property="marks_obtained", type="number", example=28.5)
     *         )
     *     ),
     *     @OA\Response(response="201", description="Mark saved and recomputation triggered")
     * )
     */
    public function store(StoreMarkRequest $request, MarksValidationService $validator): JsonResponse
    {
        $data = $request->validated();
        
        // Resolve string identifiers into IDs if needed (UI convenience)
        if (!isset($data['enrollment_id']) && isset($data['roll_number']) && isset($data['course_code'])) {
            $student = \App\Models\Student::where('roll_number', $data['roll_number'])->firstOrFail();
            $enrollment = \App\Models\Enrollment::where('student_id', $student->id)
                ->whereHas('examinationCourse.course', function($q) use ($data) {
                    $q->where('code', $data['course_code']);
                })->firstOrFail();
            $data['enrollment_id'] = $enrollment->id;
            
            if (!isset($data['assessment_component_id']) && isset($data['component_name'])) {
                $component = $enrollment->examinationCourse->assessmentComponents()
                    ->where('name', 'like', '%' . $data['component_name'] . '%')
                    ->firstOrFail();
                $data['assessment_component_id'] = $component->id;
            }
        }

        $mark = DB::transaction(function () use ($data, $validator, $request) {
            $existing = Mark::where('enrollment_id', $data['enrollment_id'])
                ->where('assessment_component_id', $data['assessment_component_id'])
                ->lockForUpdate()
                ->first();

            if ($existing && $request->filled('expected_version')
                && $existing->version !== (int) $request->input('expected_version')) {
                abort(409, 'Mark was modified by another request; refresh and retry.');
            }

            $component = \App\Models\AssessmentComponent::findOrFail($data['assessment_component_id']);
            [$status, $error] = $validator->validate((float) $data['marks_obtained'], (float) $component->max_marks);

            return Mark::updateOrCreate(
                [
                    'enrollment_id' => $data['enrollment_id'],
                    'assessment_component_id' => $data['assessment_component_id'],
                ],
                [
                    'marks_obtained' => $data['marks_obtained'],
                    'status' => $status,
                    'validation_error' => $error,
                    'entered_by' => $request->user()?->id,
                    'version' => DB::raw('COALESCE(version, 0) + 1'),
                ]
            );
        });

        // Recompute this student's result asynchronously — never inline on
        // the request thread, since one edit can cascade into a recompute
        // that touches several tables.
        CalculateStudentResult::dispatch($mark->enrollment_id)->onQueue('results');

        return response()->json($mark->fresh(), 201);
    }
}
