<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mark_upload_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mark_upload_batch_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('row_number');
            $table->json('raw_row');
            $table->string('error_message');
            $table->timestamps();

            $table->index('mark_upload_batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mark_upload_errors');
    }
};
