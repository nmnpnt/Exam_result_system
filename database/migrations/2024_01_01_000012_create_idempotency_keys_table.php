<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Generic idempotency store for any mutating API endpoint (see
     * App\Http\Middleware\EnsureIdempotencyKey). Keyed on the client-supplied
     * key + a hash of the request body, so replaying the exact same request
     * returns the original response instead of re-executing side effects.
     */
    public function up(): void
    {
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->string('idempotency_key', 100);
            $table->string('request_path');
            $table->string('request_hash', 64);
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->json('response_body')->nullable();
            $table->enum('status', ['in_progress', 'completed'])->default('in_progress');
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();

            $table->unique(['idempotency_key', 'request_path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
    }
};
