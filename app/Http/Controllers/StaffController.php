<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class StaffController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('Staff/Dashboard');
    }

    public function absensi()
    {
        return Inertia::render('Staff/Absensi');
    }

    public function perpustakaan()
    {
        return Inertia::render('Staff/Perpustakaan');
    }

    public function gaji()
    {
        return Inertia::render('Staff/SlipGaji');
    }
}
