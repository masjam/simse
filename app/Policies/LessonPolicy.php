<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    /**
     * Determine whether the user can view or download the lesson material.
     */
    public function view(User $user, Lesson $lesson): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->hasRole('Siswa') && $user->student) {
            return $user->student->student_class_id === $lesson->student_class_id;
        }

        if ($user->teacher) {
            return $user->teacher->id === $lesson->teacher_id;
        }

        return false;
    }
}
