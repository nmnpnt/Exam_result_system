<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Programme;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Programme $programme)
    {
        return $programme->courses()->paginate(100);
    }

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
