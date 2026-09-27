<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One computed result per enrollment. Recomputation is idempotent —
     * CalculateStudentResult always upserts this single row rather than
     * inserting a new one, guarded by a row lock on the enrollment during
     * the compute step (see README: Concurrency strategy).
     */
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete()->unique();
            $table->decimal('total_marks_obtained', 7, 2)->nullable();
            $table->decimal('total_max_marks', 7, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->string('grade', 4)->nullable();
            $table->enum('outcome', ['pass', 'fail', 'incomplete'])->nullable();

            // pending: not yet computed | computed: calculated, not visible to students
            // published: visible to students | withheld: manually held back (e.g. discrepancy)
            $table->enum('status', ['pending', 'computed', 'published', 'withheld'])->default('pending');
            $table->timestamp('computed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
