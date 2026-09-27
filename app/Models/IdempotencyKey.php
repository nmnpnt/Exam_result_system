<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdempotencyKey extends Model
{
    protected $fillable = [
        'idempotency_key', 'request_path', 'request_hash',
        'response_status', 'response_body', 'status', 'locked_at',
    ];

    protected $casts = [
        'response_body' => 'array',
        'locked_at' => 'datetime',
    ];
}
