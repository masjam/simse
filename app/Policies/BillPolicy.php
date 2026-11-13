<?php

namespace App\Policies;

use App\Models\Bill;
use App\Models\User;

class BillPolicy
{
    public function view(User $user, Bill $bill): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Bendahara'])) {
            return true;
        }

        if ($user->student && $user->student->id === $bill->student_id) {
            return true;
        }

        if ($bill->student && $bill->student->parent_user_id === $user->id) {
            return true;
        }

        return false;
    }

    public function manage(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Bendahara']);
    }

    public function delete(User $user, Bill $bill): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Bendahara']);
    }
}
