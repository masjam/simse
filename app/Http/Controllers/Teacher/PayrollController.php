<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Payroll;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PayrollController extends BaseTeacherController
{
    public function index(): Response
    {
        $teacher = $this->getTeacher();
        $teacherId = $teacher?->id ?? Teacher::value('id');

        $payrolls = Payroll::with('components')
            ->where('teacher_id', $teacherId)
            ->latest()
            ->get();

        if ($payrolls->isEmpty()) {
            $p = Payroll::create([
                'teacher_id' => $teacherId,
                'month' => 'Oktober',
                'year' => '2026',
                'total_salary' => 5450000,
                'status' => 'Dibayar',
            ]);
            $p->components()->createMany([
                ['type' => 'Penerimaan', 'name' => 'Gaji Pokok Pendidik', 'amount' => 3500000],
                ['type' => 'Penerimaan', 'name' => 'Tunjangan Fungsional Guru', 'amount' => 850000],
                ['type' => 'Penerimaan', 'name' => 'Insentif Wali Kelas & Pembiasaan Sholat', 'amount' => 600000],
                ['type' => 'Penerimaan', 'name' => 'Uang Makan & Kehadiran (22 Hari)', 'amount' => 660000],
                ['type' => 'Potongan', 'name' => 'Iuran BPJS Ketenagakerjaan & Kesehatan', 'amount' => 110000],
                ['type' => 'Potongan', 'name' => 'Simpanan Pokok & Wajib Koperasi', 'amount' => 50000],
            ]);
            $payrolls = Payroll::with('components')->where('id', $p->id)->get();
        }

        $latestPayroll = $payrolls->first();
        $earnings = $latestPayroll->components->where('type', 'Penerimaan')->map(fn ($c) => ['label' => $c->name, 'amount' => (float) $c->amount])->values();
        $deductions = $latestPayroll->components->where('type', 'Potongan')->map(fn ($c) => ['label' => $c->name, 'amount' => (float) $c->amount])->values();

        $slipGaji = [
            'month' => $latestPayroll->month.' '.$latestPayroll->year,
            'name' => Auth::user()?->name ?? 'Siti Guru, S.Pd.I',
            'nip' => $teacher?->nip ?? '198407152010012015',
            'jabatan' => 'Guru Pendidikan Agama Islam & Budi Pekerti',
            'golongan' => 'Penata Muda / III-A',
            'status' => strtolower($latestPayroll->status) === 'dibayar' ? 'paid' : 'pending',
            'paidDate' => '25 '.$latestPayroll->month.' '.$latestPayroll->year,
            'earnings' => $earnings,
            'deductions' => $deductions,
            'totalSalary' => (float) $latestPayroll->total_salary,
        ];

        $payrollHistory = $payrolls->map(function ($p) {
            return [
                'id' => $p->id,
                'month' => $p->month.' '.$p->year,
                'takeHome' => (float) $p->total_salary,
                'status' => strtolower($p->status) === 'dibayar' ? 'paid' : 'pending',
                'date' => '25 '.$p->month.' '.$p->year,
            ];
        });

        return Inertia::render('Teacher/Payroll', [
            'slipGaji' => $slipGaji,
            'payrollHistory' => $payrollHistory,
        ]);
    }
}
