<?php

namespace App\Http\Controllers\Student;

use App\Models\Bill;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KeuanganController extends BaseStudentController
{
    /**
     * Tampilkan ikhtisar keuangan siswa: saldo dompet digital & riwayat tagihan SPP (hanya-baca).
     * PIN dompet TIDAK PERNAH dikirim ke props atau ditampilkan di UI.
     */
    public function index(Request $request): Response
    {
        $student = $this->resolveStudent();
        $header = $this->formatStudentHeader($student);

        // Dompet Digital
        $wallet = Wallet::where('student_id', $student->id)->first();
        $walletData = [
            'id' => $wallet?->id,
            'balance' => (float) ($wallet?->balance ?? 0),
            'daily_limit' => (float) ($wallet?->daily_limit ?? 0),
            'is_active' => true,
            'rfid_card_id' => $student->rfid_uid ? 'Terdaftar (••••)' : 'Belum Ditautkan',
        ];

        // Mutasi Transaksi Dompet
        $transactions = [];
        if ($wallet) {
            $transactions = WalletTransaction::where('wallet_id', $wallet->id)
                ->latest()
                ->take(50)
                ->get()
                ->map(fn ($t) => [
                    'id' => $t->id,
                    'type' => $t->type,
                    'amount' => (float) $t->amount,
                    'status' => $t->status,
                    'description' => $t->description ?? ($t->type === 'Setoran' ? 'Top-up Saldo' : 'Transaksi Kantin/Sekolah'),
                    'created_at' => $t->created_at?->translatedFormat('d M Y, H:i') ?? '-',
                ])
                ->all();
        }

        // Tagihan SPP & Iuran Sekolah (Hanya-baca)
        $bills = Bill::with(['billType', 'payments'])
            ->where('student_id', $student->id)
            ->latest()
            ->get()
            ->map(function ($b) {
                $total = (float) $b->amount_due;
                $paid = (float) $b->payments->where('status', 'Sukses')->sum('amount_paid');
                $remaining = max(0, $total - $paid);

                return [
                    'id' => $b->id,
                    'title' => $b->billType?->name ?? 'Tagihan Sekolah',
                    'period' => $b->month ? "Bulan {$b->month} {$b->year}" : ($b->billType?->period ?? '-'),
                    'amount' => $total,
                    'paid_amount' => $paid,
                    'remaining_amount' => $remaining,
                    'status' => $b->status, // Belum Lunas, Angsuran, Lunas
                    'due_date' => $b->created_at?->translatedFormat('d M Y') ?? '-',
                ];
            })
            ->all();

        $totalRemainingBills = collect($bills)->where('status', '!=', 'Lunas')->sum('remaining_amount');

        return Inertia::render('Student/Keuangan', [
            'student' => $header,
            'wallet' => $walletData,
            'transactions' => $transactions,
            'bills' => $bills,
            'summary' => [
                'wallet_balance' => $walletData['balance'],
                'total_remaining_bills' => $totalRemainingBills,
            ],
        ]);
    }
}
