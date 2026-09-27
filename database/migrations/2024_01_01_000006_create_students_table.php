<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained()->restrictOnDelete();
            $table->string('roll_number', 30)->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->year('batch_year');
            $table->timestamps();

            // Designed for 1,000,000+ rows: lookups are always by roll_number
            // or programme_id, both indexed.
            $table->index(['programme_id', 'batch_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
