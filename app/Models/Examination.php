<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Examination extends Model
{
    use HasFactory;

    protected $fillable = [
        'programme_id', 'name', 'academic_year', 'term',
        'status', 'starts_at', 'ends_at', 'published_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function examinationCourses(): HasMany
    {
        return $this->hasMany(ExaminationCourse::class);
    }

    public function markUploadBatches(): HasMany
    {
        return $this->hasMany(MarkUploadBatch::class);
    }

    public function isOpenForMarksEntry(): bool
    {
        return $this->status === 'open';
    }
}
