<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('academic_year', 9); // e.g. 2025-2026
            $table->string('term', 20);          // e.g. Semester 1

            // draft: being configured | open: marks entry allowed
            // locked: no further mark changes | computing: results being calculated
            // published: results visible to students
            $table->enum('status', ['draft', 'open', 'locked', 'computing', 'published'])
                ->default('draft');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['programme_id', 'academic_year', 'term']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examinations');
    }
};
