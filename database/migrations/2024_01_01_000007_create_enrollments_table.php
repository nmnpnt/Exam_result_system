<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A student's registration for one course within one examination.
     * This is the row that marks and the final result key against —
     * at 100,000+ students per examination x multiple courses, this table
     * is the largest in the system and every hot-path query must hit an index.
     */
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('examination_course_id')->constrained('examination_course')->cascadeOnDelete();
            $table->enum('status', ['registered', 'withdrawn'])->default('registered');
            $table->timestamps();

            $table->unique(['student_id', 'examination_course_id'], 'enrollments_student_exam_course_unique');
            $table->index('examination_course_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
