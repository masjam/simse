<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    /**
     * Determine whether the authenticated parent can view the given child's portal data.
     */
    public function viewAsParent(User $user, Student $student): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $student->parent_user_id !== null
            && $student->parent_user_id === $user->id;
    }
}
