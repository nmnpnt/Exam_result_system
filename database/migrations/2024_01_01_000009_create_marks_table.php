<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per (enrollment, assessment_component). This is the table the
     * bulk CSV import writes into, at up to millions of rows overall — the
     * composite unique key below is what makes upserts idempotent: importing
     * the same CSV twice (or retrying a failed chunk) overwrites the same
     * rows instead of duplicating them.
     */
    public function up(): void
    {
        Schema::create('marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_component_id')->constrained()->cascadeOnDelete();
            $table->decimal('marks_obtained', 6, 2);

            // pending: awaiting validation | valid: within range and accepted
            // rejected: failed validation (see validation_error) and excluded from results
            $table->enum('status', ['pending', 'valid', 'rejected'])->default('pending');
            $table->string('validation_error')->nullable();

            $table->foreignId('mark_upload_batch_id')->nullable()
                ->constrained('mark_upload_batches')->nullOnDelete();
            $table->unsignedBigInteger('entered_by')->nullable(); // staff/user id, kept loose on purpose
            $table->unsignedInteger('version')->default(1); // optimistic-lock counter for concurrent edits
            $table->timestamps();

            $table->unique(['enrollment_id', 'assessment_component_id'], 'marks_enrollment_component_unique');
            $table->index('assessment_component_id');
            $table->index('mark_upload_batch_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marks');
    }
};
