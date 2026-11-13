<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAchievement extends Model
{
    use HasUuids;

    protected $fillable = [
        'student_id',
        'name',
        'category',
        'level',
        'year',
        'organizer',
        'achievement_date',
        'description',
        'certificate_path',
        'certificate_original_name',
        'status',
        'submitted_by',
        'verified_by',
        'verified_at',
        'verification_notes',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'achievement_date' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope achievements that are verified/approved for official e-Raport display.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'Disetujui');
    }
}
