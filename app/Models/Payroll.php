<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    use HasUuids;

    protected $fillable = [
        'teacher_id',
        'user_id',
        'month',
        'year',
        'total_salary',
        'status',
    ];

    protected $casts = [
        'total_salary' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(PayrollComponent::class);
    }
}
