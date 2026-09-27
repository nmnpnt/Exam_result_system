<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarkUploadBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'examination_id', 'idempotency_key', 'original_filename', 'storage_path',
        'total_rows', 'processed_rows', 'valid_rows', 'failed_rows',
        'total_chunks', 'completed_chunks', 'status', 'uploaded_by',
        'started_at', 'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function examination(): BelongsTo
    {
        return $this->belongsTo(Examination::class);
    }

    public function errors(): HasMany
    {
        return $this->hasMany(MarkUploadError::class);
    }

    public function isComplete(): bool
    {
        return $this->completed_chunks >= $this->total_chunks && $this->total_chunks > 0;
    }
}
