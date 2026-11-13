<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Payroll;

abstract class BaseBendaharaController extends Controller
{
    protected function formatRupiah(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    protected function recalculateBillStatus(Bill $bill): void
    {
        $totalVerified = (float) $bill->payments()
            ->where('status', 'Berhasil')
            ->sum('amount_paid');

        if ($totalVerified >= (float) $bill->amount_due) {
            $status = 'Lunas';
        } elseif ($totalVerified > 0) {
            $status = 'Angsuran';
        } else {
            $status = 'Belum Lunas';
        }

        $bill->update(['status' => $status]);
    }

    protected function recalculatePayrollTotal(Payroll $payroll): void
    {
        $income = (float) $payroll->components()->where('type', 'Penerimaan')->sum('amount');
        $deduction = (float) $payroll->components()->where('type', 'Potongan')->sum('amount');

        $payroll->update([
            'total_salary' => max(0, $income - $deduction),
        ]);
    }

    protected function indonesianMonthName(int $monthNumber): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $months[$monthNumber] ?? 'Januari';
    }
}
