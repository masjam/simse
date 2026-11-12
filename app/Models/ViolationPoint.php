<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ViolationPoint extends Model
{
    protected $fillable = [
        'name',
        'points',
        'category',
    ];

    public function studentViolations(): HasMany
    {
        return $this->hasMany(StudentViolation::class);
    }
}
