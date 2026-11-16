<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    use HasUuids;

    protected $fillable = [
        'exam_id',
        'student_id',
        'answers',
        'question_order',
        'seed',
        'extra_time_minutes',
        'is_unlocked',
        'manual_essay_scores',
        'needs_manual_grading',
        'started_at',
        'finished_at',
        'is_forced_submit',
        'score',
    ];

    protected $casts = [
        'answers' => 'array',
        'question_order' => 'array',
        'seed' => 'integer',
        'extra_time_minutes' => 'integer',
        'is_unlocked' => 'boolean',
        'manual_essay_scores' => 'array',
        'needs_manual_grading' => 'boolean',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'is_forced_submit' => 'boolean',
        'score' => 'float',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function violations(): HasMany
    {
        return $this->hasMany(ExamViolation::class);
    }
}
