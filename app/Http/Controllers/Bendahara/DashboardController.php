<?php

namespace App\Http\Controllers\Bendahara;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\Wallet;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends BaseBendaharaController
{
    public function index(): Response
    {
        $verifiedPaymentsTotal = (float) Payment::where('status', 'Berhasil')->sum('amount_paid');
        $paidPayrollTotal = (float) Payroll::where('status', 'Dibayar')->sum('total_salary');
        $totalWalletDeposits = (float) Wallet::sum('balance');
        $activeCash = max(0, $verifiedPaymentsTotal + $totalWalletDeposits - $paidPayrollTotal);

        $currentMonthPayments = (float) Payment::where('status', 'Berhasil')
            ->whereYear('payment_date', Carbon::now()->year)
            ->whereMonth('payment_date', Carbon::now()->month)
            ->sum('amount_paid');

        $unpaidBills = Bill::with(['payments' => fn ($q) => $q->where('status', 'Berhasil')])
            ->where('status', '!=', 'Lunas')
            ->get();

        $totalArrears = $unpaidBills->sum(function (Bill $bill): float {
            $paid = (float) $bill->payments->sum('amount_paid');

            return max(0, (float) $bill->amount_due - $paid);
        });

        $pendingVerificationsCount = Payment::where('status', 'Menunggu Verifikasi')->count();
        $draftPayrollsCount = Payroll::where('status', 'Draft')->count();

        $recentPayments = Payment::with(['bill.billType', 'bill.student.user', 'bill.student.studentClass'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function (Payment $payment): array {
                return [
                    'id' => $payment->id,
                    'title' => ($payment->bill?->billType?->name ?? 'Pembayaran').($payment->bill?->month ? ' - '.$payment->bill->month : ''),
                    'subtitle' => ($payment->bill?->student?->user?->name ?? 'Siswa').' • '.($payment->bill?->student?->studentClass?->name ?? '-'),
                    'category' => 'Pemasukan',
                    'status' => $payment->status,
                    'date' => Carbon::parse($payment->payment_date)->format('d M Y'),
                    'amount' => (float) $payment->amount_paid,
                    'timestamp' => $payment->created_at?->timestamp ?? 0,
                ];
            });

        $recentPayrolls = Payroll::with(['teacher.user'])
            ->where('status', 'Dibayar')
            ->latest()
            ->take(3)
            ->get()
            ->map(function (Payroll $payroll): array {
                return [
                    'id' => $payroll->id,
                    'title' => "Pencairan Gaji {$payroll->month} {$payroll->year}",
                    'subtitle' => $payroll->teacher?->user?->name ?? 'Pegawai',
                    'category' => 'Pengeluaran',
                    'status' => $payroll->status,
                    'date' => $payroll->updated_at?->format('d M Y') ?? '-',
                    'amount' => (float) $payroll->total_salary,
                    'timestamp' => $payroll->updated_at?->timestamp ?? 0,
                ];
            });

        $recentTransactions = $recentPayments
            ->concat($recentPayrolls)
            ->sortByDesc('timestamp')
            ->take(8)
            ->values();

        return Inertia::render('Bendahara/Dashboard', [
            'stats' => [
                'total_kas' => $this->formatRupiah($activeCash),
                'spp_terkumpul' => $this->formatRupiah($currentMonthPayments),
                'tunggakan_spp' => $this->formatRupiah($totalArrears),
                'payroll_terbayar' => $this->formatRupiah($paidPayrollTotal),
                'pending_verifications' => $pendingVerificationsCount,
                'draft_payrolls' => $draftPayrollsCount,
                'total_dompet_siswa' => $this->formatRupiah($totalWalletDeposits),
            ],
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
