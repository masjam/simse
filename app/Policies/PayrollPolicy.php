<?php

namespace App\Policies;

use App\Models\Payroll;
use App\Models\User;

class PayrollPolicy
{
    public function view(User $user, Payroll $payroll): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Bendahara'])) {
            return true;
        }

        if ($payroll->user_id && $user->id === $payroll->user_id) {
            return true;
        }

        if ($payroll->teacher_id && $user->teacher && $user->teacher->id === $payroll->teacher_id) {
            return true;
        }

        return false;
    }

    public function manage(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Bendahara']);
    }
}
