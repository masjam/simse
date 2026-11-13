<?php

namespace App\Policies;

use App\Models\StudentAchievement;
use App\Models\User;

class StudentAchievementPolicy
{
    /**
     * Determine whether the user can verify or reject a student achievement.
     */
    public function verify(User $user, StudentAchievement $achievement): bool
    {
        return $user->hasAnyRole([
            'Super Admin',
            'Guru BK',
            'Admin Tata Usaha',
            'Staf Sekolah',
            'Guru Pengajar',
            'Bidang Lomba',
        ]);
    }

    /**
     * Determine whether the user can view or download the achievement certificate.
     */
    public function viewCertificate(User $user, StudentAchievement $achievement): bool
    {
        if ($this->verify($user, $achievement)) {
            return true;
        }

        $student = $achievement->student;

        if (! $student) {
            return false;
        }

        return ($student->parent_user_id !== null && $student->parent_user_id === $user->id)
            || ($student->user_id !== null && $student->user_id === $user->id);
    }
}
