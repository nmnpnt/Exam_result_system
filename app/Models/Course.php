<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['programme_id', 'code', 'name', 'credits'];

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function examinationCourses(): HasMany
    {
        return $this->hasMany(ExaminationCourse::class);
    }
}
