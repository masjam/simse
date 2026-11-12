<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingSchedule extends Model
{
    protected $fillable = [
        'teacher_id', 'subject_id', 'student_class_id', 'jpl',
        'day', 'time_start', 'time_end', 'room',
    ];

    protected $casts = [
        'day' => 'integer',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function studentClass(): BelongsTo
    {
        return $this->belongsTo(StudentClass::class);
    }

    /**
     * Map day number to Indonesian day name.
     */
    public function getDayNameAttribute(): string
    {
        return match ($this->day) {
            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
            4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu',
            default => '-',
        };
    }
}
