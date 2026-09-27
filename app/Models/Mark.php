<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mark extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id', 'assessment_component_id', 'marks_obtained',
        'status', 'validation_error', 'mark_upload_batch_id', 'entered_by', 'version',
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function assessmentComponent(): BelongsTo
    {
        return $this->belongsTo(AssessmentComponent::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MarkUploadBatch::class, 'mark_upload_batch_id');
    }
}
