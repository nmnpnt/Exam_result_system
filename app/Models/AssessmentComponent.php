<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentComponent extends Model
{
    use HasFactory;

    protected $fillable = ['examination_course_id', 'name', 'max_marks', 'weight_percentage'];

    public function examinationCourse(): BelongsTo
    {
        return $this->belongsTo(ExaminationCourse::class, 'examination_course_id');
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class);
    }
}
