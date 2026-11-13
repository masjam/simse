<?php

namespace App\Http\Controllers\Bendahara;

use App\Models\Bill;
use App\Models\BillType;
use App\Models\Student;
use App\Models\StudentClass;
use App\Notifications\SimseNotification;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SppController extends BaseBendaharaController
{
    public function index(): Response
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
            'parent',
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
            $formattedArrears = 'Rp '.number_format($totalArrears, 0, ',', '.');
            $message = "Assalamu'alaikum Bapak/Ibu Wali dari {$studentName}, kami informasikan terdapat tagihan sekolah sebesar {$formattedArrears} yang belum diselesaikan. Terima kasih.";

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
}
