<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;

class AssignmentPolicy
{
    /**
     * Determine whether the student can submit an answer for the assignment.
     */
    public function submit(User $user, Assignment $assignment): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->hasRole('Siswa') && $user->student) {
            $assignment->loadMissing('lesson');

            return $assignment->lesson?->student_class_id === $user->student->student_class_id;
        }

        return false;
    }
}
