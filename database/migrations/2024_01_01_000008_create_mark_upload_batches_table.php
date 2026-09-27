<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tracks one bulk CSV upload end-to-end so a client can poll progress
     * on a 100,000+ row file instead of holding an HTTP connection open.
     */
    public function up(): void
    {
        Schema::create('mark_upload_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('examination_id')->constrained()->cascadeOnDelete();
            $table->string('idempotency_key', 100)->unique();
            $table->string('original_filename');
            $table->string('storage_path');
            $table->unsignedBigInteger('total_rows')->default(0);
            $table->unsignedBigInteger('processed_rows')->default(0);
            $table->unsignedBigInteger('valid_rows')->default(0);
            $table->unsignedBigInteger('failed_rows')->default(0);
            $table->unsignedInteger('total_chunks')->default(0);
            $table->unsignedInteger('completed_chunks')->default(0);

            // queued: chunks dispatched | processing: workers consuming chunks
            // completed: all chunks done | failed: unrecoverable error before chunking
            $table->enum('status', ['queued', 'processing', 'completed', 'failed'])->default('queued');
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('examination_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mark_upload_batches');
    }
};
