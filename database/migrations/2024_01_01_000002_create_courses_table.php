<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('name');
            $table->unsignedTinyInteger('credits')->default(3);
            $table->timestamps();

            // Unique within a programme; 10,000+ courses expected overall.
            $table->unique(['programme_id', 'code']);
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
