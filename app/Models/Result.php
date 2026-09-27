<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id', 'total_marks_obtained', 'total_max_marks', 'percentage',
        'grade', 'outcome', 'status', 'computed_at', 'published_at',
    ];

    protected $casts = [
        'computed_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }
}
