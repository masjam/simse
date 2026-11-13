<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Bill;
use App\Models\BillType;
use App\Models\CanteenOrder;
use App\Models\CanteenStand;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\PayrollComponent;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Notifications\SimseNotification;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BendaharaController extends Controller
{
    private function formatRupiah(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    private function recalculateBillStatus(Bill $bill): void
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

    private function recalculatePayrollTotal(Payroll $payroll): void
    {
        $income = (float) $payroll->components()->where('type', 'Penerimaan')->sum('amount');
        $deduction = (float) $payroll->components()->where('type', 'Potongan')->sum('amount');

        $payroll->update([
            'total_salary' => max(0, $income - $deduction),
        ]);
    }

    private function indonesianMonthName(int $monthNumber): string
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $months[$monthNumber] ?? 'Januari';
    }

    // 1. DASHBOARD KEUANGAN
    public function dashboard(): Response
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

    // 2. KASIR PEMBAYARAN & VERIFIKASI TRANSFER
    public function kasir(Request $request): Response
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

    // 3. MANAJEMEN SPP & POS TAGIHAN
    public function spp(): Response
    {
        $billTypes = BillType::withCount('bills')
            ->orderBy('name')
            ->get()
            ->map(function (BillType $type): array {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'amount' => (float) $type->amount,
                    'period' => $type->period,
                    'bills_count' => $type->bills_count,
                ];
            });

        $bills = Bill::with([
            'billType',
            'student.user',
            'student.studentClass',
            'payments' => fn ($q) => $q->where('status', 'Berhasil'),
        ])
            ->latest()
            ->get()
            ->map(function (Bill $bill): array {
                $paid = (float) $bill->payments->sum('amount_paid');
                $remaining = max(0, (float) $bill->amount_due - $paid);

                return [
                    'id' => $bill->id,
                    'bill_type_id' => $bill->bill_type_id,
                    'bill_name' => $bill->billType?->name ?? '-',
                    'period' => $bill->billType?->period ?? '-',
                    'student_id' => $bill->student_id,
                    'student_name' => $bill->student?->user?->name ?? 'Siswa',
                    'nisn' => $bill->student?->nisn ?? '-',
                    'class' => $bill->student?->studentClass?->name ?? '-',
                    'month' => $bill->month,
                    'year' => $bill->year,
                    'amount_due' => (float) $bill->amount_due,
                    'paid_amount' => $paid,
                    'remaining' => $remaining,
                    'status' => $bill->status,
                ];
            });

        $arrears = Student::with([
            'user',
            'studentClass',
            'bills' => fn ($q) => $q->with(['billType', 'payments' => fn ($pq) => $pq->where('status', 'Berhasil')])
                ->where('status', '!=', 'Lunas'),
        ])
            ->get()
            ->map(function (Student $student): ?array {
                if ($student->bills->isEmpty()) {
                    return null;
                }

                $totalArrears = 0.0;
                $items = [];

                foreach ($student->bills as $bill) {
                    $paid = (float) $bill->payments->sum('amount_paid');
                    $remaining = max(0, (float) $bill->amount_due - $paid);
                    if ($remaining <= 0) {
                        continue;
                    }

                    $totalArrears += $remaining;
                    $items[] = ($bill->billType?->name ?? 'Tagihan').trim(' '.($bill->month ?? '').' '.($bill->year ?? ''));
                }

                if ($totalArrears <= 0) {
                    return null;
                }

                return [
                    'student_id' => $student->id,
                    'nisn' => $student->nisn ?? '-',
                    'nama' => $student->user?->name ?? 'Siswa',
                    'kelas' => $student->studentClass?->name ?? '-',
                    'phone' => $student->no_telp_ortu ?? '-',
                    'bulan' => implode(', ', $items),
                    'tunggakan' => $totalArrears,
                ];
            })
            ->filter()
            ->sortByDesc('tunggakan')
            ->values();

        $classes = StudentClass::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Bendahara/SPP', [
            'billTypes' => $billTypes,
            'bills' => $bills,
            'arrears' => $arrears,
            'classes' => $classes,
        ]);
    }

    public function storeBillType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'period' => ['required', 'string', 'in:Bulanan,Semester,Tahunan,Sekali Bayar'],
        ]);

        BillType::create($validated);

        return redirect()->back()->with('success', 'Pos tagihan master berhasil ditambahkan!');
    }

    public function updateBillType(Request $request, BillType $billType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'period' => ['required', 'string', 'in:Bulanan,Semester,Tahunan,Sekali Bayar'],
        ]);

        $billType->update($validated);

        return redirect()->back()->with('success', 'Pos tagihan berhasil diperbarui!');
    }

    public function destroyBillType(BillType $billType): RedirectResponse
    {
        $billType->delete();

        return redirect()->back()->with('success', 'Pos tagihan berhasil dihapus.');
    }

    public function generateBills(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bill_type_id' => ['required', 'uuid', 'exists:bill_types,id'],
            'student_class_id' => ['nullable', 'uuid', 'exists:student_classes,id'],
            'month' => ['nullable', 'string', 'max:50'],
            'year' => ['required', 'string', 'max:10'],
            'amount_due' => ['nullable', 'numeric', 'min:1'],
        ]);

        $billType = BillType::findOrFail($validated['bill_type_id']);
        $amountDue = isset($validated['amount_due']) && (float) $validated['amount_due'] > 0
            ? (float) $validated['amount_due']
            : (float) $billType->amount;

        $studentsQuery = Student::query();
        if (! empty($validated['student_class_id'])) {
            $studentsQuery->where('student_class_id', $validated['student_class_id']);
        }

        $students = $studentsQuery->get();
        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada siswa ditemukan untuk target kelas tersebut.');
        }

        $createdCount = 0;
        DB::transaction(function () use ($students, $billType, $validated, $amountDue, &$createdCount): void {
            foreach ($students as $student) {
                $bill = Bill::firstOrCreate(
                    [
                        'bill_type_id' => $billType->id,
                        'student_id' => $student->id,
                        'month' => $validated['month'] ?: null,
                        'year' => $validated['year'],
                    ],
                    [
                        'status' => 'Belum Lunas',
                        'amount_due' => $amountDue,
                    ]
                );

                if ($bill->wasRecentlyCreated) {
                    $createdCount++;
                }
            }
        });

        return redirect()->back()->with('success', "Distribusi tagihan selesai: {$createdCount} tagihan baru diterbitkan.");
    }

    public function destroyBill(Bill $bill): RedirectResponse
    {
        if ($bill->payments()->where('status', 'Berhasil')->exists()) {
            return redirect()->back()->with('error', 'Tagihan yang sudah memiliki pembayaran berhasil tidak dapat dihapus.');
        }

        $bill->delete();

        return redirect()->back()->with('success', 'Tagihan siswa berhasil dihapus.');
    }

    public function sendArrearsReminder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['nullable', 'uuid', 'exists:students,id'],
        ]);

        $studentsQuery = Student::with([
            'user',
            'bills' => fn ($q) => $q->with(['billType', 'payments' => fn ($pq) => $pq->where('status', 'Berhasil')])
                ->where('status', '!=', 'Lunas'),
        ]);

        if (! empty($validated['student_id'])) {
            $studentsQuery->where('id', $validated['student_id']);
        }

        $sentCount = 0;
        foreach ($studentsQuery->get() as $student) {
            if ($student->bills->isEmpty()) {
                continue;
            }

            $totalArrears = $student->bills->sum(function (Bill $bill): float {
                return max(0, (float) $bill->amount_due - (float) $bill->payments->sum('amount_paid'));
            });

            if ($totalArrears <= 0) {
                continue;
            }

            $studentName = $student->user?->name ?? 'Siswa';
            $message = "Assalamu'alaikum Bapak/Ibu Wali dari {$studentName}, kami informasikan terdapat tagihan sekolah sebesar {$this->formatRupiah($totalArrears)} yang belum diselesaikan. Terima kasih.";

            if ($student->no_telp_ortu) {
                app(WhatsAppService::class)->queue(
                    phoneNumber: $student->no_telp_ortu,
                    message: $message,
                    recipientName: $student->nama_ayah ?: ($student->nama_ibu ?: "Wali {$studentName}"),
                );
            }

            if ($student->parent) {
                $student->parent->notify(new SimseNotification(
                    title: "Pengingat Tagihan SPP {$studentName}",
                    message: $message,
                    category: 'billing',
                    url: route('ortu.dashboard', ['tab' => 'keuangan']),
                ));
            }
            $sentCount++;
        }

        return redirect()->back()->with('success', "Pengingat tagihan WhatsApp tercatat dikirim untuk {$sentCount} wali siswa.");
    }

    // 4. TABUNGAN & DOMPET DIGITAL SISWA
    public function dompet(): Response
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

    public function storeWalletTransaction(Request $request): RedirectResponse
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
                throw ValidationException::withMessages([
                    'amount' => 'Saldo dompet siswa tidak mencukupi (Saldo: '.$this->formatRupiah((float) $lockedWallet->balance).').',
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

    public function updateWalletPin(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'pin' => ['required', 'digits:6'],
        ]);

        $wallet = Wallet::firstOrCreate(
            ['student_id' => $student->id],
            ['balance' => 0]
        );

        $wallet->update([
            'pin' => $validated['pin'],
        ]);

        return redirect()->back()->with('success', 'PIN 6-digit dompet digital siswa berhasil diperbarui.');
    }

    // 5. PAYROLL & PENGGAJIAN PEGAWAI
    public function payroll(Request $request): Response
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

    public function generatePayroll(Request $request): RedirectResponse
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

    public function storePayrollComponent(Request $request, Payroll $payroll): RedirectResponse
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

    public function destroyPayrollComponent(PayrollComponent $component): RedirectResponse
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

    public function updatePayrollStatus(Request $request, Payroll $payroll): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:Draft,Menunggu Persetujuan,Dibayar'],
        ]);

        $payroll->update([
            'status' => $validated['status'],
        ]);

        return redirect()->back()->with('success', "Status payroll berhasil diperbarui menjadi {$validated['status']}.");
    }

    public function submitPayrollApproval(Request $request): RedirectResponse
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

    public function kantinReport(Request $request): Response
    {
        $startDate = (string) $request->query('start_date', today()->startOfMonth()->toDateString());
        $endDate = (string) $request->query('end_date', today()->toDateString());

        $stands = CanteenStand::with('owner')
            ->orderBy('name')
            ->get()
            ->map(function (CanteenStand $stand) use ($startDate, $endDate): array {
                $ordersQuery = CanteenOrder::where('canteen_stand_id', $stand->id)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);

                $validOrders = (clone $ordersQuery)->where('status', '!=', 'Dibatalkan');
                $completedOrders = (clone $ordersQuery)->where('status', 'Selesai');

                return [
                    'id' => $stand->id,
                    'name' => $stand->name,
                    'owner_name' => $stand->owner?->name ?? 'Pengelola Stand',
                    'is_open' => $stand->is_open,
                    'balance' => (float) $stand->balance,
                    'total_orders' => (clone $validOrders)->count(),
                    'completed_orders' => (clone $completedOrders)->count(),
                    'total_revenue' => (float) (clone $validOrders)->sum('total_price'),
                ];
            })
            ->values();

        $recentOrders = CanteenOrder::with(['stand', 'student.user', 'items'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->latest()
            ->take(30)
            ->get()
            ->map(fn (CanteenOrder $order): array => [
                'id' => $order->id,
                'qr_code_pickup' => $order->qr_code_pickup,
                'stand_name' => $order->stand?->name ?? '-',
                'student_name' => $order->student?->user?->name ?? 'Siswa',
                'total_price' => (float) $order->total_price,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'created_at' => $order->created_at?->format('d M Y, H:i') ?? '-',
                'items_summary' => $order->items->map(fn ($i): string => "{$i->menu_name} x{$i->quantity}")->implode(', '),
            ])
            ->values();

        return Inertia::render('Bendahara/KantinReport', [
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => [
                'total_stands' => $stands->count(),
                'total_orders' => $stands->sum('total_orders'),
                'total_revenue' => (float) $stands->sum('total_revenue'),
                'total_stand_balance' => (float) $stands->sum('balance'),
            ],
            'stands' => $stands,
            'recentOrders' => $recentOrders,
        ]);
    }

    public function updateStudentRfid(Request $request, Student $student): RedirectResponse
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
