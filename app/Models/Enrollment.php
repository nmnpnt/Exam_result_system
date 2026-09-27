<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'examination_course_id', 'status'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function examinationCourse(): BelongsTo
    {
        return $this->belongsTo(ExaminationCourse::class, 'examination_course_id');
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(Result::class);
    }
}
