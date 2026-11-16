<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use Auditable, HasUuids, SoftDeletes;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'duration_minutes',
        'max_violations',
        'scoring_rule',
        'randomize_questions',
        'randomize_options',
        'is_remedial',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'randomize_questions' => 'boolean',
        'randomize_options' => 'boolean',
        'is_remedial' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function violations(): HasMany
    {
        return $this->hasMany(ExamViolation::class);
    }

    public function targets(): HasMany
    {
        return $this->hasMany(ExamStudentTarget::class);
    }
}
