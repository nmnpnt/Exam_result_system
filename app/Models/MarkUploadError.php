<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarkUploadError extends Model
{
    protected $fillable = ['mark_upload_batch_id', 'row_number', 'raw_row', 'error_message'];

    protected $casts = [
        'raw_row' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MarkUploadBatch::class, 'mark_upload_batch_id');
    }
}
