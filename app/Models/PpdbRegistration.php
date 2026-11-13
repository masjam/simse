<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpdbRegistration extends Model
{
    use HasUuids;

    protected $fillable = [
        'registration_number',
        'student_name',
        'nik',
        'nisn',
        'gender',
        'birth_place',
        'birth_date',
        'address',
        'previous_school',
        'parent_name',
        'parent_phone',
        'parent_email',
        'parent_job',
        'kk_path',
        'birth_certificate_path',
        'ijazah_path',
        'status',
        'admin_notes',
        'student_id',
        'student_user_id',
        'parent_user_id',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function studentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }

    public function parentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }
}

