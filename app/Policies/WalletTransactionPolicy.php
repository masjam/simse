<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WalletTransaction;

class WalletTransactionPolicy
{
    public function view(User $user, WalletTransaction $transaction): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Bendahara'])) {
            return true;
        }

        $wallet = $transaction->wallet;
        if (! $wallet) {
            return false;
        }

        if ($user->student && $user->student->id === $wallet->student_id) {
            return true;
        }

        if ($wallet->student && $wallet->student->parent_user_id === $user->id) {
            return true;
        }

        return false;
    }
}
