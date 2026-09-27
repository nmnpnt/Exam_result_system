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
     * POST /api/marks — single-record entry/correction path (as opposed to
     * bulk CSV). Optimistic locking via `version`: a client must send back
     * the version it last read when updating, or the write is rejected.
     */
    public function store(StoreMarkRequest $request, MarksValidationService $validator): JsonResponse
    {
        $data = $request->validated();

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
