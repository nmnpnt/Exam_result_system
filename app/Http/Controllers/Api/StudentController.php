<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/students",
     *     tags={"Students"},
     *     summary="List all students",
     *     @OA\Parameter(name="roll_number", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response="200", description="A list of students")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Student::query()
            ->when($request->programme_id, fn ($q, $id) => $q->where('programme_id', $id))
            ->when($request->roll_number, fn ($q, $rn) => $q->where('roll_number', $rn));

        return response()->json($query->paginate(50));
    }

    /**
     * @OA\Post(
     *     path="/api/students",
     *     tags={"Students"},
     *     summary="Create a new student",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="programme_id", type="integer", example=1),
     *             @OA\Property(property="roll_number", type="string", example="CSE2026001"),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="batch_year", type="integer", example=2026)
     *         )
     *     ),
     *     @OA\Response(response="201", description="Student created")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'programme_id' => 'required|exists:programmes,id',
            'roll_number' => 'required|string|max:30|unique:students,roll_number',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'batch_year' => 'nullable|integer|min:2000|max:2100',
        ]);

        return response()->json(Student::create($data), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/students/{student}",
     *     tags={"Students"},
     *     summary="Show a student with their enrolments",
     *     @OA\Parameter(name="student", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Student details with enrolments")
     * )
     */
    public function show(Student $student): JsonResponse
    {
        return response()->json($student->load('programme', 'enrollments'));
    }
}
