<?php

use App\Http\Controllers\Api\AssessmentComponentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\ExaminationController;
use App\Http\Controllers\Api\MarkController;
use App\Http\Controllers\Api\MarkUploadController;
use App\Http\Controllers\Api\ProgrammeController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\StudentController;
use Illuminate\Support\Facades\Route;

// ── Public auth routes (no token required) ──────────────────────────────
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

// ── Protected routes (Sanctum bearer token required) ────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource('programmes', ProgrammeController::class)->only(['index', 'store', 'show']);
    Route::apiResource('programmes.courses', CourseController::class)->only(['index', 'store'])->shallow();

    Route::apiResource('examinations', ExaminationController::class)->only(['index', 'store', 'show']);
    Route::patch('examinations/{examination}/status', [ExaminationController::class, 'updateStatus']);
    Route::post('examinations/{examination}/courses', [ExaminationController::class, 'addCourse']);

    // Assessment components for an examination course.
    Route::get('examination-courses/{examinationCourse}/components', [AssessmentComponentController::class, 'index']);
    Route::post('examination-courses/{examinationCourse}/components', [AssessmentComponentController::class, 'store']);

    // Students.
    Route::apiResource('students', StudentController::class)->only(['index', 'store', 'show']);

    // Enrollments.
    Route::post('enrollments', [EnrollmentController::class, 'store']);

    // Single-record marks entry/correction.
    Route::post('marks', [MarkController::class, 'store']);

    // Bulk CSV upload — idempotency key required.
    Route::middleware('idempotent')->group(function () {
        Route::post('examinations/{examination}/marks/upload', [MarkUploadController::class, 'store']);
    });
    Route::get('mark-uploads/{batch}', [MarkUploadController::class, 'show'])->name('mark-uploads.show');
    Route::get('mark-uploads/{batch}/errors', [MarkUploadController::class, 'errors']);

    // Result lifecycle.
    Route::get('results', [ResultController::class, 'index']);
    Route::post('examinations/{examination}/results/compute', [ResultController::class, 'compute']);
    Route::middleware('idempotent')->group(function () {
        Route::post('examinations/{examination}/results/publish', [ResultController::class, 'publish']);
    });
    Route::get('enrollments/{enrollment}/result', [ResultController::class, 'show']);
});
