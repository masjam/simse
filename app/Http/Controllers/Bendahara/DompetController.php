<?php

namespace App\Http\Controllers\Bendahara;

use App\Models\Student;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class DompetController extends BaseBendaharaController
{
    public function index(): Response
    {
        $students = Student::with(['user', 'studentClass', 'wallet'])
            ->get()
            ->map(function (Student $student): array {
                return [
                    'id' => $student->id,
                    'name' => $student->user?->name ?? 'Siswa',
                    'nisn' => $student->nisn ?? '-',
                    'rfid_uid' => $student->rfid_uid,
                    'class' => $student->studentClass?->name ?? '-',
                    'wallet_id' => $student->wallet?->id,
                    'balance' => (float) ($student->wallet?->balance ?? 0),
                    'has_pin' => ! empty($student->wallet?->pin),
                ];
            })
            ->sortBy('name')
            ->values();

        $transactions = WalletTransaction::with(['wallet.student.user', 'wallet.student.studentClass'])
            ->latest()
            ->take(50)
            ->get()
            ->map(function (WalletTransaction $tx): array {
                return [
                    'id' => $tx->id,
                    'reference_id' => $tx->reference_id ?? 'WAL-'.strtoupper(substr(str_replace('-', '', $tx->id), 0, 8)),
                    'student_name' => $tx->wallet?->student?->user?->name ?? 'Siswa',
                    'class' => $tx->wallet?->student?->studentClass?->name ?? '-',
                    'type' => $tx->type,
                    'amount' => (float) $tx->amount,
                    'description' => $tx->description ?? '-',
                    'status' => $tx->status,
                    'date' => $tx->created_at?->format('d M Y H:i') ?? '-',
                ];
            })
            ->values();

        $totalBalance = (float) Wallet::sum('balance');
        $totalDeposits = (float) WalletTransaction::where('type', 'Setoran')->where('status', 'Sukses')->sum('amount');
        $totalWithdrawals = (float) WalletTransaction::whereIn('type', ['Penarikan', 'Pembayaran Kantin', 'Pembayaran SPP'])
            ->where('status', 'Sukses')
            ->sum('amount');

        return Inertia::render('Bendahara/Dompet', [
            'students' => $students,
            'transactions' => $transactions,
            'summary' => [
                'total_balance' => $totalBalance,
                'total_deposits' => $totalDeposits,
                'total_withdrawals' => $totalWithdrawals,
                'active_wallets' => Wallet::count(),
            ],
        ]);
    }

    public function storeTransaction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'uuid', 'exists:students,id'],
            'type' => ['required', 'string', 'in:Setoran,Penarikan'],
            'amount' => ['required', 'numeric', 'min:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated): void {
            $wallet = Wallet::firstOrCreate(
                ['student_id' => $validated['student_id']],
                ['balance' => 0]
            );

            /** @var Wallet $lockedWallet */
            $lockedWallet = Wallet::lockForUpdate()->findOrFail($wallet->id);
            $amount = (float) $validated['amount'];

            if ($validated['type'] === 'Penarikan' && (float) $lockedWallet->balance < $amount) {
                $formatted = 'Rp '.number_format((float) $lockedWallet->balance, 0, ',', '.');
                throw ValidationException::withMessages([
                    'amount' => "Saldo dompet siswa tidak mencukupi (Saldo: {$formatted}).",
                ]);
            }

            $newBalance = $validated['type'] === 'Setoran'
                ? (float) $lockedWallet->balance + $amount
                : (float) $lockedWallet->balance - $amount;

            $lockedWallet->update(['balance' => $newBalance]);

            WalletTransaction::create([
                'wallet_id' => $lockedWallet->id,
                'type' => $validated['type'],
                'amount' => $amount,
                'reference_id' => 'WAL-'.strtoupper(Str::random(8)),
                'description' => $validated['description'] ?: ($validated['type'].' tunai melalui Bendahara'),
                'status' => 'Sukses',
            ]);
        });

        return redirect()->back()->with('success', "Transaksi {$validated['type']} dompet digital berhasil diproses!");
    }

    public function updatePin(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'pin' => ['required', 'digits:6'],
        ]);

        $wallet = Wallet::firstOrCreate(
            ['student_id' => $student->id],
            ['balance' => 0]
        );

        $wallet->update(['pin' => $validated['pin']]);

        return redirect()->back()->with('success', 'PIN 6-digit dompet digital siswa berhasil diperbarui.');
    }

    public function updateRfid(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'rfid_uid' => ['nullable', 'string', 'max:100', 'unique:students,rfid_uid,'.$student->id],
        ], [
            'rfid_uid.unique' => 'UID kartu RFID ini sudah digunakan oleh siswa lain.',
        ]);

        $uid = $validated['rfid_uid'] ? trim($validated['rfid_uid']) : null;
        $student->update(['rfid_uid' => $uid]);

        return redirect()->back()->with(
            'success',
            $uid ? "Kartu RFID ({$uid}) berhasil ditautkan ke tabungan siswa." : 'Tautan kartu RFID siswa dilepas.'
        );
    }
}
