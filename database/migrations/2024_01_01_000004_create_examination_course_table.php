<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Which courses are offered within a given examination instance.
     * Assessment components, enrollments and results all attach to this row,
     * since the same course can recur across multiple examinations with
     * different max marks / pass marks.
     */
    public function up(): void
    {
        Schema::create('examination_course', function (Blueprint $table) {
            $table->id();
            $table->foreignId('examination_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('max_marks')->default(100);
            $table->unsignedSmallInteger('pass_marks')->default(40);
            $table->timestamps();

            $table->unique(['examination_id', 'course_id']);
            $table->index('course_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examination_course');
    }
};
