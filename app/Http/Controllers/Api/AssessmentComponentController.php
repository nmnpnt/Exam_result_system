<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssessmentComponent;
use App\Models\ExaminationCourse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentComponentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/examination-courses/{examinationCourse}/components",
     *     tags={"Assessment Components"},
     *     summary="List all assessment components for an examination course",
     *     @OA\Parameter(name="examinationCourse", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="List of components")
     * )
     */
    public function index(ExaminationCourse $examinationCourse): JsonResponse
    {
        return response()->json(
            $examinationCourse->assessmentComponents()->get()
        );
    }

    /**
     * @OA\Post(
     *     path="/api/examination-courses/{examinationCourse}/components",
     *     tags={"Assessment Components"},
     *     summary="Create a new assessment component (e.g. Internal, Final)",
     *     @OA\Parameter(name="examinationCourse", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Internal"),
     *             @OA\Property(property="max_marks", type="number", example=30),
     *             @OA\Property(property="weight_percentage", type="number", example=0.5)
     *         )
     *     ),
     *     @OA\Response(response="201", description="Component created")
     * )
     */
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
