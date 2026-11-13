<?php

namespace App\Policies;

use App\Models\TeachingJournal;
use App\Models\User;

class TeachingJournalPolicy
{
    /**
     * Determine whether the user can view the teaching journal.
     */
    public function view(User $user, TeachingJournal $teachingJournal): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->teacher && $user->teacher->id === $teachingJournal->teacher_id) {
            return true;
        }

        if ($user->hasRole('Siswa') && $user->student) {
            return $user->student->student_class_id === $teachingJournal->student_class_id;
        }

        return false;
    }

    /**
     * Determine whether the teacher can update the journal.
     */
    public function update(User $user, TeachingJournal $teachingJournal): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->teacher?->id === $teachingJournal->teacher_id;
    }

    /**
     * Determine whether the teacher can delete the journal.
     */
    public function delete(User $user, TeachingJournal $teachingJournal): bool
    {
        return $this->update($user, $teachingJournal);
    }
}
