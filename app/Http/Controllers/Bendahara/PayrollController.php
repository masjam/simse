<?php

namespace App\Http\Controllers\Bendahara;

use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\PayrollComponent;
use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PayrollController extends BaseBendaharaController
{
    public function index(Request $request): Response
    {
        $selectedMonth = $request->query('month', $this->indonesianMonthName(Carbon::now()->month));
        $selectedYear = (string) $request->query('year', (string) Carbon::now()->year);

        $payrolls = Payroll::with(['teacher.user', 'user', 'components'])
            ->where('month', $selectedMonth)
            ->where('year', $selectedYear)
            ->latest()
            ->get()
            ->map(function (Payroll $payroll): array {
                $income = (float) $payroll->components->where('type', 'Penerimaan')->sum('amount');
                $deduction = (float) $payroll->components->where('type', 'Potongan')->sum('amount');
                $basicSalary = (float) $payroll->components
                    ->where('type', 'Penerimaan')
                    ->filter(fn ($c) => stripos($c->name, 'Pokok') !== false)
                    ->sum('amount');
                $allowance = max(0, $income - $basicSalary);

                $employeeName = $payroll->teacher?->user?->name ?? ($payroll->user?->name ?? 'Pegawai');
                $position = $payroll->teacher_id ? 'Guru & Tenaga Pendidik' : 'Staf Tenaga Kependidikan';

                return [
                    'id' => $payroll->id,
                    'teacher_id' => $payroll->teacher_id,
                    'user_id' => $payroll->user_id,
                    'nama' => $employeeName,
                    'nip' => $payroll->teacher?->nip ?? '-',
                    'posisi' => $position,
                    'month' => $payroll->month,
                    'year' => $payroll->year,
                    'gapok' => $basicSalary,
                    'tunjangan' => $allowance,
                    'potongan' => $deduction,
                    'total' => (float) $payroll->total_salary,
                    'status' => $payroll->status,
                    'components' => $payroll->components->map(fn (PayrollComponent $c): array => [
                        'id' => $c->id,
                        'type' => $c->type,
                        'name' => $c->name,
                        'amount' => (float) $c->amount,
                    ])->values(),
                ];
            })
            ->values();

        $summary = [
            'draft_total' => (float) $payrolls->where('status', 'Draft')->sum('total'),
            'pending_total' => (float) $payrolls->where('status', 'Menunggu Persetujuan')->sum('total'),
            'paid_total' => (float) $payrolls->where('status', 'Dibayar')->sum('total'),
        ];

        return Inertia::render('Bendahara/Payroll', [
            'payrolls' => $payrolls,
            'summary' => $summary,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'string', 'max:30'],
            'year' => ['required', 'string', 'max:10'],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
        ]);

        $teachers = Teacher::with(['user', 'schedules'])->get();
        $staffUsers = Role::where('name', 'Staf Sekolah')->exists()
            ? User::role('Staf Sekolah')->get()
            : collect();

        if ($teachers->isEmpty() && $staffUsers->isEmpty()) {
            return redirect()->back()->with('error', 'Belum ada data guru atau staf terdaftar untuk digenerate.');
        }

        $baseSalary = isset($validated['base_salary']) ? (float) $validated['base_salary'] : 3500000.0;
        $generatedCount = 0;

        DB::transaction(function () use ($teachers, $staffUsers, $validated, $baseSalary, &$generatedCount): void {
            foreach ($teachers as $teacher) {
                $exists = Payroll::where('teacher_id', $teacher->id)
                    ->where('month', $validated['month'])
                    ->where('year', $validated['year'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                $attendanceDays = Attendance::where('user_id', $teacher->user_id)
                    ->where('status', 'Hadir')
                    ->whereYear('date', (int) $validated['year'])
                    ->count();

                $totalJpl = (int) $teacher->schedules->sum('jpl');
                $teachingAllowance = $totalJpl > 0 ? $totalJpl * 75000 : 500000;
                $attendanceAllowance = $attendanceDays * 25000;
                $bpjsDeduction = 100000.0;
                $netSalary = max(0, $baseSalary + $teachingAllowance + $attendanceAllowance - $bpjsDeduction);

                $payroll = Payroll::create([
                    'teacher_id' => $teacher->id,
                    'user_id' => $teacher->user_id,
                    'month' => $validated['month'],
                    'year' => $validated['year'],
                    'total_salary' => $netSalary,
                    'status' => 'Draft',
                ]);

                PayrollComponent::create([
                    'payroll_id' => $payroll->id,
                    'type' => 'Penerimaan',
                    'name' => 'Gaji Pokok Pendidik',
                    'amount' => $baseSalary,
                ]);

                PayrollComponent::create([
                    'payroll_id' => $payroll->id,
                    'type' => 'Penerimaan',
                    'name' => $totalJpl > 0 ? "Honor Mengajar ({$totalJpl} JPL)" : 'Tunjangan Fungsional Guru',
                    'amount' => $teachingAllowance,
                ]);

                if ($attendanceAllowance > 0) {
                    PayrollComponent::create([
                        'payroll_id' => $payroll->id,
                        'type' => 'Penerimaan',
                        'name' => "Tunjangan Kehadiran ({$attendanceDays} Hari)",
                        'amount' => $attendanceAllowance,
                    ]);
                }

                PayrollComponent::create([
                    'payroll_id' => $payroll->id,
                    'type' => 'Potongan',
                    'name' => 'Iuran BPJS Kesehatan',
                    'amount' => $bpjsDeduction,
                ]);

                $generatedCount++;
            }

            foreach ($staffUsers as $staffUser) {
                $exists = Payroll::where('user_id', $staffUser->id)
                    ->where('month', $validated['month'])
                    ->where('year', $validated['year'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                $attendanceDays = Attendance::where('user_id', $staffUser->id)
                    ->where('status', 'Hadir')
                    ->whereYear('date', (int) $validated['year'])
                    ->count();

                $staffBase = $baseSalary * 0.9;
                $attendanceAllowance = $attendanceDays * 25000;
                $bpjsDeduction = 100000.0;
                $netSalary = max(0, $staffBase + $attendanceAllowance - $bpjsDeduction);

                $payroll = Payroll::create([
                    'teacher_id' => null,
                    'user_id' => $staffUser->id,
                    'month' => $validated['month'],
                    'year' => $validated['year'],
                    'total_salary' => $netSalary,
                    'status' => 'Draft',
                ]);

                PayrollComponent::create([
                    'payroll_id' => $payroll->id,
                    'type' => 'Penerimaan',
                    'name' => 'Gaji Pokok Tenaga Kependidikan',
                    'amount' => $staffBase,
                ]);

                if ($attendanceAllowance > 0) {
                    PayrollComponent::create([
                        'payroll_id' => $payroll->id,
                        'type' => 'Penerimaan',
                        'name' => "Tunjangan Kehadiran Staf ({$attendanceDays} Hari)",
                        'amount' => $attendanceAllowance,
                    ]);
                }

                PayrollComponent::create([
                    'payroll_id' => $payroll->id,
                    'type' => 'Potongan',
                    'name' => 'Iuran BPJS Kesehatan',
                    'amount' => $bpjsDeduction,
                ]);

                $generatedCount++;
            }
        });

        return redirect()->back()->with('success', "Generate payroll selesai: {$generatedCount} slip gaji baru dibuat.");
    }

    public function storeComponent(Request $request, Payroll $payroll): RedirectResponse
    {
        if ($payroll->status === 'Dibayar') {
            return redirect()->back()->with('error', 'Komponen payroll yang sudah dibayar tidak dapat diubah.');
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:Penerimaan,Potongan'],
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        DB::transaction(function () use ($payroll, $validated): void {
            PayrollComponent::create([
                'payroll_id' => $payroll->id,
                'type' => $validated['type'],
                'name' => $validated['name'],
                'amount' => (float) $validated['amount'],
            ]);

            $this->recalculatePayrollTotal($payroll);
        });

        return redirect()->back()->with('success', 'Komponen gaji berhasil ditambahkan!');
    }

    public function destroyComponent(PayrollComponent $component): RedirectResponse
    {
        $payroll = $component->payroll;
        if ($payroll && $payroll->status === 'Dibayar') {
            return redirect()->back()->with('error', 'Komponen payroll yang sudah dibayar tidak dapat dihapus.');
        }

        DB::transaction(function () use ($component, $payroll): void {
            $component->delete();
            if ($payroll) {
                $this->recalculatePayrollTotal($payroll);
            }
        });

        return redirect()->back()->with('success', 'Komponen gaji berhasil dihapus.');
    }

    public function updateStatus(Request $request, Payroll $payroll): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:Draft,Menunggu Persetujuan,Dibayar'],
        ]);

        $payroll->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', "Status payroll berhasil diperbarui menjadi {$validated['status']}.");
    }

    public function submitApproval(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'string', 'max:30'],
            'year' => ['required', 'string', 'max:10'],
        ]);

        $updatedCount = Payroll::where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->where('status', 'Draft')
            ->update(['status' => 'Menunggu Persetujuan']);

        return redirect()->back()->with('success', "{$updatedCount} draft payroll diajukan untuk persetujuan.");
    }
}
