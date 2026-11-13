<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wallet;

class WalletPolicy
{
    public function view(User $user, Wallet $wallet): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Bendahara'])) {
            return true;
        }

        if ($user->student && $user->student->id === $wallet->student_id) {
            return true;
        }

        if ($wallet->student && $wallet->student->parent_user_id === $user->id) {
            return true;
        }

        return false;
    }

    public function update(User $user, Wallet $wallet): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Bendahara'])) {
            return true;
        }

        // Parent can update child's daily limit
        if ($wallet->student && $wallet->student->parent_user_id === $user->id) {
            return true;
        }

        return false;
    }
}
