<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pivot-with-identity for the examination<->course relationship: it carries
 * its own id because assessment_components and enrollments hang off it.
 */
class ExaminationCourse extends Model
{
    use HasFactory;

    protected $table = 'examination_course';

    protected $fillable = ['examination_id', 'course_id', 'max_marks', 'pass_marks'];

    public function examination(): BelongsTo
    {
        return $this->belongsTo(Examination::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function assessmentComponents(): HasMany
    {
        return $this->hasMany(AssessmentComponent::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}
