<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class BendaharaController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('Bendahara/Dashboard', [
            'stats' => [
                'total_kas' => 'Rp 145.500.000',
                'spp_terkumpul' => 'Rp 32.000.000',
                'tunggakan_spp' => 'Rp 5.400.000',
                'payroll_terbayar' => 'Rp 85.000.000'
            ]
        ]);
    }

    public function spp()
    {
        return Inertia::render('Bendahara/SPP');
    }

    public function kasir()
    {
        return Inertia::render('Bendahara/Kasir');
    }

    public function payroll()
    {
        return Inertia::render('Bendahara/Payroll');
    }
}
