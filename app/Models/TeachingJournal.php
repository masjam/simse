<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingJournal extends Model
{
    use HasUuids;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'student_class_id',
        'teaching_schedule_id',
        'date',
        'meeting_number',
        'topic',
        'summary',
        'activities',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'meeting_number' => 'integer',
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

    public function teachingSchedule(): BelongsTo
    {
        return $this->belongsTo(TeachingSchedule::class);
    }
}
