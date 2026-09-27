<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Programme;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/programmes/{programme}/courses",
     *     tags={"Courses"},
     *     summary="List all courses in a programme",
     *     @OA\Parameter(name="programme", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Paginated list of courses")
     * )
     */
    public function index(Programme $programme)
    {
        return $programme->courses()->paginate(100);
    }

    /**
     * @OA\Post(
     *     path="/api/programmes/{programme}/courses",
     *     tags={"Courses"},
     *     summary="Create a new course in a programme",
     *     @OA\Parameter(name="programme", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="string", example="CS301"),
     *             @OA\Property(property="name", type="string", example="Database Systems"),
     *             @OA\Property(property="credits", type="integer", example=4)
     *         )
     *     ),
     *     @OA\Response(response="201", description="Course created")
     * )
     */
    public function store(Request $request, Programme $programme)
    {
        $data = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'credits' => 'nullable|integer|min:1|max:10',
        ]);

        $data['programme_id'] = $programme->id;

        return response()->json(Course::create($data), 201);
    }
}
