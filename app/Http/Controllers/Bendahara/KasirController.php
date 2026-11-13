<?php

namespace App\Http\Controllers\Bendahara;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KasirController extends BaseBendaharaController
{
    public function index(Request $request): Response
    {
        $students = Student::with([
            'user',
            'studentClass',
            'wallet',
            'bills' => fn ($q) => $q->with(['billType', 'payments'])->orderByDesc('created_at'),
        ])
            ->get()
            ->map(function (Student $student): array {
                $bills = $student->bills->map(function (Bill $bill): array {
                    $paidAmount = (float) $bill->payments->where('status', 'Berhasil')->sum('amount_paid');
                    $remaining = max(0, (float) $bill->amount_due - $paidAmount);

                    return [
                        'id' => $bill->id,
                        'name' => $bill->billType?->name ?? 'Tagihan',
                        'period' => trim(($bill->month ?? '').' '.($bill->year ?? '')),
                        'amount_due' => (float) $bill->amount_due,
                        'paid_amount' => $paidAmount,
                        'remaining' => $remaining,
                        'status' => $bill->status,
                    ];
                })->values();

                $unpaidCount = $bills->where('status', '!=', 'Lunas')->count();

                return [
                    'id' => $student->id,
                    'name' => $student->user?->name ?? 'Siswa',
                    'nisn' => $student->nisn ?? '-',
                    'rfid_uid' => $student->rfid_uid,
                    'class' => $student->studentClass?->name ?? '-',
                    'wallet_balance' => (float) ($student->wallet?->balance ?? 0),
                    'unpaid_count' => $unpaidCount,
                    'bills' => $bills,
                ];
            })
            ->sortBy('name')
            ->values();

        $pendingPayments = Payment::with(['bill.billType', 'bill.student.user', 'bill.student.studentClass'])
            ->where('status', 'Menunggu Verifikasi')
            ->latest()
            ->get()
            ->map(function (Payment $payment): array {
                return [
                    'id' => $payment->id,
                    'student_name' => $payment->bill?->student?->user?->name ?? 'Siswa',
                    'nisn' => $payment->bill?->student?->nisn ?? '-',
                    'class' => $payment->bill?->student?->studentClass?->name ?? '-',
                    'bill_name' => $payment->bill?->billType?->name ?? 'Tagihan',
                    'period' => trim(($payment->bill?->month ?? '').' '.($payment->bill?->year ?? '')),
                    'amount_paid' => (float) $payment->amount_paid,
                    'payment_method' => $payment->payment_method,
                    'payment_date' => Carbon::parse($payment->payment_date)->format('d M Y'),
                    'receipt_url' => $payment->receipt_photo ? route('bendahara.payments.receipt', $payment->id) : null,
                ];
            })
            ->values();

        $recentPayments = Payment::with(['bill.billType', 'bill.student.user', 'bill.student.studentClass', 'cashier'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function (Payment $payment): array {
                return [
                    'id' => $payment->id,
                    'trx_code' => 'TRX-'.strtoupper(substr(str_replace('-', '', $payment->id), 0, 8)),
                    'student_name' => $payment->bill?->student?->user?->name ?? 'Siswa',
                    'class' => $payment->bill?->student?->studentClass?->name ?? '-',
                    'bill_name' => $payment->bill?->billType?->name ?? 'Tagihan',
                    'amount_paid' => (float) $payment->amount_paid,
                    'payment_method' => $payment->payment_method,
                    'payment_date' => Carbon::parse($payment->payment_date)->format('d M Y'),
                    'status' => $payment->status,
                    'cashier_name' => $payment->cashier?->name ?? 'Bendahara',
                    'receipt_url' => $payment->receipt_photo ? route('bendahara.payments.receipt', $payment->id) : null,
                ];
            })
            ->values();

        return Inertia::render('Bendahara/Kasir', [
            'students' => $students,
            'pendingPayments' => $pendingPayments,
            'recentPayments' => $recentPayments,
            'initialStudentId' => $request->query('student_id'),
        ]);
    }

    public function storePayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bill_id' => ['required', 'uuid', 'exists:bills,id'],
            'amount_paid' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string', 'in:Tunai,Transfer Bank,Dompet Digital'],
            'payment_date' => ['nullable', 'date'],
            'receipt_photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'is_pending_verification' => ['nullable', 'boolean'],
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt_photo')) {
            $receiptPath = $request->file('receipt_photo')->store('receipts', 'private');
        }

        try {
            $trxCode = DB::transaction(function () use ($validated, $receiptPath): string {
                /** @var Bill $bill */
                $bill = Bill::with('billType')->lockForUpdate()->findOrFail($validated['bill_id']);

                $alreadyPaid = (float) $bill->payments()
                    ->where('status', 'Berhasil')
                    ->sum('amount_paid');
                $remaining = max(0, (float) $bill->amount_due - $alreadyPaid);

                if ($remaining <= 0) {
                    throw ValidationException::withMessages([
                        'bill_id' => 'Tagihan ini sudah berstatus Lunas.',
                    ]);
                }

                $amountPaid = (float) $validated['amount_paid'];
                if ($amountPaid > $remaining) {
                    throw ValidationException::withMessages([
                        'amount_paid' => 'Nominal pembayaran melebihi sisa tagihan ('.$this->formatRupiah($remaining).').',
                    ]);
                }

                $status = ! empty($validated['is_pending_verification'])
                    ? 'Menunggu Verifikasi'
                    : 'Berhasil';

                $payment = Payment::create([
                    'bill_id' => $bill->id,
                    'user_id' => Auth::id(),
                    'amount_paid' => $amountPaid,
                    'payment_method' => $validated['payment_method'],
                    'receipt_photo' => $receiptPath,
                    'payment_date' => $validated['payment_date'] ?? Carbon::today()->toDateString(),
                    'status' => $status,
                ]);

                if ($validated['payment_method'] === 'Dompet Digital' && $status === 'Berhasil') {
                    /** @var Wallet|null $wallet */
                    $wallet = Wallet::where('student_id', $bill->student_id)->lockForUpdate()->first();
                    if (! $wallet || (float) $wallet->balance < $amountPaid) {
                        throw ValidationException::withMessages([
                            'payment_method' => 'Saldo Dompet Digital siswa tidak mencukupi untuk pembayaran ini.',
                        ]);
                    }

                    $wallet->update([
                        'balance' => (float) $wallet->balance - $amountPaid,
                    ]);

                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'type' => 'Pembayaran SPP',
                        'amount' => $amountPaid,
                        'reference_id' => $payment->id,
                        'description' => 'Pembayaran '.($bill->billType?->name ?? 'Tagihan').($bill->month ? " ({$bill->month} {$bill->year})" : ''),
                        'status' => 'Sukses',
                    ]);
                }

                $this->recalculateBillStatus($bill);

                return 'TRX-'.strtoupper(substr(str_replace('-', '', $payment->id), 0, 8));
            });
        } catch (\Throwable $e) {
            if ($receiptPath && Storage::disk('private')->exists($receiptPath)) {
                Storage::disk('private')->delete($receiptPath);
            }

            throw $e;
        }

        return redirect()->back()->with('success', "Pembayaran {$trxCode} berhasil dicatat!");
    }

    public function verifyPayment(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'string', 'in:Berhasil,Ditolak'],
        ]);

        DB::transaction(function () use ($payment, $validated): void {
            /** @var Payment $lockedPayment */
            $lockedPayment = Payment::lockForUpdate()->findOrFail($payment->id);
            $lockedPayment->update([
                'status' => $validated['decision'],
                'user_id' => Auth::id(),
            ]);

            if ($lockedPayment->bill) {
                $this->recalculateBillStatus($lockedPayment->bill);
            }
        });

        $label = $validated['decision'] === 'Berhasil' ? 'diverifikasi (Berhasil)' : 'ditolak';

        return redirect()->back()->with('success', "Pembayaran berhasil {$label}.");
    }

    public function downloadReceipt(Payment $payment): StreamedResponse
    {
        if (! $payment->receipt_photo || ! Storage::disk('private')->exists($payment->receipt_photo)) {
            abort(404);
        }

        return Storage::disk('private')->download($payment->receipt_photo);
    }
}
