<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    /**
     * Determine whether the user can view or download the submission attachment.
     */
    public function view(User $user, Submission $submission): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->hasRole('Siswa') && $user->student) {
            return $user->student->id === $submission->student_id;
        }

        if ($user->teacher) {
            $submission->loadMissing('assignment.lesson');

            return $submission->assignment?->lesson?->teacher_id === $user->teacher->id;
        }

        return false;
    }
}
