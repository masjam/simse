<?php

namespace App\Services\Payroll;

use App\Models\Advance;
use App\Models\AdvanceInstallment;
use App\Models\Payroll;
use App\Models\PayrollComponent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AdvanceService
{
    /**
     * Ajukan kasbon baru untuk pegawai (guru / staf).
     */
    public function requestAdvance(User $user, float $amount, int $installmentCount, string $reason): Advance
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Nominal kasbon harus lebih dari 0.');
        }

        if ($installmentCount < 1 || $installmentCount > 12) {
            throw new InvalidArgumentException('Tenor angsuran kasbon antara 1 sampai 12 bulan.');
        }

        // Cek kasbon aktif yang belum selesai
        $hasActiveAdvance = Advance::where('user_id', $user->id)
            ->whereIn('status', ['Diajukan', 'Disetujui'])
            ->exists();

        if ($hasActiveAdvance) {
            throw new InvalidArgumentException('Anda masih memiliki pengajuan atau tanggungan kasbon aktif yang belum lunas.');
        }

        $monthlyAmount = round($amount / $installmentCount, 2);

        return Advance::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'installment_count' => $installmentCount,
            'monthly_amount' => $monthlyAmount,
            'reason' => $reason,
            'status' => 'Diajukan',
        ]);
    }

    /**
     * Setujui pengajuan kasbon dan buat jadwal angsuran bulanan.
     */
    public function approveAdvance(Advance $advance, User $approver): Advance
    {
        if ($advance->status !== 'Diajukan') {
            throw new InvalidArgumentException('Hanya pengajuan kasbon berstatus Diajukan yang dapat disetujui.');
        }

        return DB::transaction(function () use ($advance, $approver): Advance {
            $advance->update([
                'status' => 'Disetujui',
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'disbursed_at' => now(),
            ]);

            // Buat jadwal angsuran bulanan mulai bulan berjalan/berikutnya
            $startMonth = Carbon::now()->addMonth();

            for ($i = 1; $i <= $advance->installment_count; $i++) {
                $targetDate = (clone $startMonth)->addMonths($i - 1);

                AdvanceInstallment::create([
                    'advance_id' => $advance->id,
                    'installment_number' => $i,
                    'amount' => $advance->monthly_amount,
                    'month' => $targetDate->translatedFormat('F'),
                    'year' => (string) $targetDate->year,
                    'status' => 'Pending',
                ]);
            }

            return $advance->fresh(['installments']);
        });
    }

    /**
     * Tolak pengajuan kasbon dengan alasan resmi.
     */
    public function rejectAdvance(Advance $advance, User $approver, string $reason): Advance
    {
        if ($advance->status !== 'Diajukan') {
            throw new InvalidArgumentException('Hanya pengajuan kasbon berstatus Diajukan yang dapat ditolak.');
        }

        $advance->update([
            'status' => 'Ditolak',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $advance;
    }

    /**
     * Terapkan potongan angsuran kasbon ke slip gaji payroll pegawai.
     */
    public function applyPendingInstallmentToPayroll(Payroll $payroll): ?AdvanceInstallment
    {
        if (! $payroll->user_id) {
            return null;
        }

        $pendingInstallment = AdvanceInstallment::whereHas('advance', function ($query) use ($payroll) {
            $query->where('user_id', $payroll->user_id)
                ->where('status', 'Disetujui');
        })
            ->where('status', 'Pending')
            ->orderBy('installment_number')
            ->first();

        if (! $pendingInstallment) {
            return null;
        }

        return DB::transaction(function () use ($payroll, $pendingInstallment): AdvanceInstallment {
            $advance = $pendingInstallment->advance;

            PayrollComponent::create([
                'payroll_id' => $payroll->id,
                'type' => 'Potongan',
                'name' => "Angsuran Kasbon (#{$pendingInstallment->installment_number}/{$advance->installment_count})",
                'amount' => $pendingInstallment->amount,
            ]);

            $payroll->total_salary = max(0, $payroll->total_salary - $pendingInstallment->amount);
            $payroll->save();

            $pendingInstallment->update([
                'payroll_id' => $payroll->id,
                'status' => 'Lunas',
                'paid_at' => now(),
            ]);

            // Cek apakah seluruh angsuran pada pinjaman ini sudah lunas
            $remaining = AdvanceInstallment::where('advance_id', $advance->id)
                ->where('status', 'Pending')
                ->count();

            if ($remaining === 0) {
                $advance->update(['status' => 'Lunas']);
            }

            return $pendingInstallment;
        });
    }
}
