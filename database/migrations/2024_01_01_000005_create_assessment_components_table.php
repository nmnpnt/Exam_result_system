<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('examination_course_id')->constrained('examination_course')->cascadeOnDelete();
            $table->string('name');          // e.g. Internal, Midterm, Final, Practical
            $table->unsignedSmallInteger('max_marks');
            $table->decimal('weight_percentage', 5, 2)->default(100.00);
            $table->timestamps();

            $table->index('examination_course_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_components');
    }
};
