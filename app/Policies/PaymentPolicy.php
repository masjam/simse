<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Bendahara'])) {
            return true;
        }

        $bill = $payment->bill;
        if (! $bill) {
            return false;
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
}
