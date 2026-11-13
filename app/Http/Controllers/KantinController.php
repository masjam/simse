<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class KantinController extends Controller
{
    public function index(): RedirectResponse
    {
        $user = Auth::user();

        if ($user?->hasRole('Siswa')) {
            return redirect()->route('siswa.kantin');
        }

        if ($user?->hasRole('Pengelola Stand Kantin')) {
            return redirect()->route('kantin.dashboard');
        }

        if ($user?->hasAnyRole(['Bendahara', 'Super Admin'])) {
            return redirect()->route('bendahara.kantin');
        }

        return redirect()->route('dashboard');
    }
}
