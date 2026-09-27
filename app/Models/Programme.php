<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Programme extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'duration_years'];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function examinations(): HasMany
    {
        return $this->hasMany(Examination::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
