<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Programme;
use Illuminate\Http\Request;

class ProgrammeController extends Controller
{
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

    public function show(Programme $programme)
    {
        return $programme->load('courses');
    }
}
