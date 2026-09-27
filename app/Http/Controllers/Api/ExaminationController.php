<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Examination;
use Illuminate\Http\Request;

class ExaminationController extends Controller
{
    public function index(Request $request)
    {
        return Examination::query()
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->programme_id, fn ($q, $id) => $q->where('programme_id', $id))
            ->paginate(50);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'programme_id' => 'required|exists:programmes,id',
            'name' => 'required|string|max:255',
            'academic_year' => 'required|string|max:9',
            'term' => 'required|string|max:20',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
        ]);

        return response()->json(Examination::create($data), 201);
    }

    public function show(Examination $examination)
    {
        return $examination->load('examinationCourses.course', 'examinationCourses.assessmentComponents');
    }

    /** PATCH /api/examinations/{examination}/status — drive the status lifecycle. */
    public function updateStatus(Request $request, Examination $examination)
    {
        $data = $request->validate([
            'status' => 'required|in:draft,open,locked,computing,published',
        ]);

        $examination->update(['status' => $data['status']]);

        return $examination;
    }

    public function addCourse(Request $request, Examination $examination)
    {
        $data = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'max_marks' => 'nullable|integer|min:1',
            'pass_marks' => 'nullable|integer|min:0',
        ]);

        $examinationCourse = $examination->examinationCourses()->create($data);

        return response()->json($examinationCourse, 201);
    }
}
