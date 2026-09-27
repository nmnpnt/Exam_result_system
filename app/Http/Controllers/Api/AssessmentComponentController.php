<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssessmentComponent;
use App\Models\ExaminationCourse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentComponentController extends Controller
{
    /** GET /api/examination-courses/{examinationCourse}/components */
    public function index(ExaminationCourse $examinationCourse): JsonResponse
    {
        return response()->json(
            $examinationCourse->assessmentComponents()->get()
        );
    }

    /** POST /api/examination-courses/{examinationCourse}/components */
    public function store(Request $request, ExaminationCourse $examinationCourse): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'max_marks' => 'required|numeric|min:1',
            'weight_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $component = $examinationCourse->assessmentComponents()->create($data);

        return response()->json($component, 201);
    }
}
