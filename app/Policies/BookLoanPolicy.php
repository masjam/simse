<?php

namespace App\Policies;

use App\Models\BookLoan;
use App\Models\User;

class BookLoanPolicy
{
    public function view(User $user, BookLoan $bookLoan): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Staf Sekolah'])) {
            return true;
        }

        return $user->id === $bookLoan->user_id;
    }

    public function manage(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Staf Sekolah']);
    }
}
