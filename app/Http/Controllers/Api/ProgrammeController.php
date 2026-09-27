<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Programme;
use Illuminate\Http\Request;

class ProgrammeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/programmes",
     *     tags={"Programmes"},
     *     summary="List all programmes",
     *     @OA\Response(response="200", description="Paginated list of programmes")
     * )
     */
    public function index()
    {
        return Programme::paginate(50);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:20|unique:programmes,code',
            'name' => 'required|string|max:255',
            'duration_years' => 'nullable|integer|min:1|max:10',
        ]);

        return response()->json(Programme::create($data), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/programmes/{programme}",
     *     tags={"Programmes"},
     *     summary="Get a specific programme with its courses",
     *     @OA\Parameter(name="programme", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Programme details")
     * )
     */
    public function show(Programme $programme)
    {
        return $programme->load('courses');
    }
}
