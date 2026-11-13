<?php

namespace App\Policies;

use App\Models\ExamAttempt;
use App\Models\User;

class ExamAttemptPolicy
{
    /**
     * Determine whether the user can view or update the exam attempt.
     */
    public function update(User $user, ExamAttempt $examAttempt): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->hasRole('Siswa') && $user->student) {
            return $user->student->id === $examAttempt->student_id;
        }

        return false;
    }
}
