<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Payroll;
use App\Services\Pdf\PdfRenderer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DocumentController extends Controller
{
    public function __construct(
        protected PdfRenderer $pdfRenderer
    ) {}

    /**
     * Download or stream official payment receipt as PDF.
     */
    public function paymentReceipt(Request $request, Payment $payment): Response
    {
        $user = $request->user();
        $payment->loadMissing(['bill.student.user', 'bill.student.studentClass', 'bill.billType', 'cashier']);

        $student = $payment->bill?->student;

        // Otorisasi: Bendahara, Super Admin, atau Siswa/Orang Tua pemilik tagihan
        $isOwner = false;
        if ($user->hasRole('Siswa') && $student?->user_id === $user->id) {
            $isOwner = true;
        } elseif ($user->hasRole('Orang Tua') && $student?->parent_user_id === $user->id) {
            $isOwner = true;
        }

        if (! $user->hasRole('Super Admin') && ! $user->hasRole('Bendahara') && ! $isOwner) {
            abort(403, 'Anda tidak memiliki hak akses melihat kuitansi ini.');
        }

        $verification = $this->pdfRenderer->createVerification(
            documentType: 'Bukti Pembayaran',
            recipientName: $student?->user?->name ?? 'Siswa',
            reference: $payment,
            metadata: [
                'bill_name' => $payment->bill?->billType?->name,
                'amount' => $payment->amount_paid,
                'date' => $payment->payment_date?->format('Y-m-d'),
            ],
            title: "Kuitansi Pembayaran {$payment->bill?->billType?->name} - {$student?->user?->name}"
        );

        $data = [
            'title' => "Kuitansi Pembayaran - {$student?->user?->name}",
            'receiptNumber' => 'KW/'.date('Ym').'/'.substr($payment->id, 0, 6),
            'studentName' => $student?->user?->name ?? 'Siswa',
            'studentNis' => $student?->nis ?? '-',
            'studentNisn' => $student?->nisn ?? '-',
            'className' => $student?->studentClass?->name ?? '-',
            'amount' => $payment->amount_paid,
            'terbilang' => $this->terbilang($payment->amount_paid).' rupiah',
            'billName' => $payment->bill?->billType?->name ?? 'Iuran Sekolah',
            'month' => $payment->bill?->month ? "{$payment->bill->month} {$payment->bill->year}" : date('F Y'),
            'paymentMethod' => $payment->payment_method,
            'cashierName' => $payment->cashier?->name ?? 'Bendahara Sekolah',
        ];

        return $this->pdfRenderer->stream(
            view: 'pdf.bukti-pembayaran',
            data: $data,
            filename: "Kuitansi_{$payment->id}.pdf",
            verification: $verification
        );
    }

    /**
     * Download or stream official payroll slip as PDF.
     */
    public function payrollSlip(Request $request, Payroll $payroll): Response
    {
        $user = $request->user();
        $payroll->loadMissing(['user', 'teacher.user', 'components']);

        $employeeUser = $payroll->user ?: ($payroll->teacher?->user);

        // Otorisasi: Bendahara, Super Admin, atau Pegawai bersangkutan
        if (! $user->hasRole('Super Admin') && ! $user->hasRole('Bendahara') && $user->id !== $employeeUser?->id) {
            abort(403, 'Anda tidak memiliki hak akses melihat slip gaji ini.');
        }

        $earnings = $payroll->components->where('type', 'Tunjangan')->map(fn ($c) => [
            'name' => $c->name,
            'amount' => (float) $c->amount,
        ])->values()->all();

        $deductions = $payroll->components->where('type', 'Potongan')->map(fn ($c) => [
            'name' => $c->name,
            'amount' => (float) $c->amount,
        ])->values()->all();

        $verification = $this->pdfRenderer->createVerification(
            documentType: 'Slip Gaji',
            recipientName: $employeeUser?->name ?? 'Pegawai',
            reference: $payroll,
            metadata: [
                'month' => $payroll->month,
                'year' => $payroll->year,
                'total' => $payroll->total_salary,
            ],
            title: "Slip Honorarium {$payroll->month} {$payroll->year} - {$employeeUser?->name}"
        );

        $data = [
            'title' => "Slip Gaji {$payroll->month} {$payroll->year} - {$employeeUser?->name}",
            'month' => $payroll->month,
            'year' => $payroll->year,
            'employeeName' => $employeeUser?->name ?? 'Pegawai',
            'employeeNip' => $payroll->teacher?->nip ?? 'NBM. 1092384',
            'position' => $payroll->teacher ? 'Tenaga Pendidik / Guru' : 'Tenaga Kependidikan / Staf',
            'earnings' => $earnings,
            'deductions' => $deductions,
            'basicSalary' => (float) $payroll->total_salary,
            'treasurerName' => 'Dra. Hj. Siti Maryam, M.Pd.',
        ];

        return $this->pdfRenderer->stream(
            view: 'pdf.slip-gaji',
            data: $data,
            filename: "Slip_Gaji_{$payroll->month}_{$payroll->year}_{$employeeUser?->name}.pdf",
            verification: $verification
        );
    }

    private function terbilang(float $number): string
    {
        $number = abs($number);
        $huruf = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($number < 12) {
            return $huruf[(int) $number];
        } elseif ($number < 20) {
            return $this->terbilang($number - 10).' belas';
        } elseif ($number < 100) {
            return $this->terbilang(floor($number / 10)).' puluh '.$this->terbilang($number % 10);
        } elseif ($number < 200) {
            return 'seratus '.$this->terbilang($number - 100);
        } elseif ($number < 1000) {
            return $this->terbilang(floor($number / 100)).' ratus '.$this->terbilang($number % 100);
        } elseif ($number < 2000) {
            return 'seribu '.$this->terbilang($number - 1000);
        } elseif ($number < 1000000) {
            return $this->terbilang(floor($number / 1000)).' ribu '.$this->terbilang($number % 1000);
        } elseif ($number < 1000000000) {
            return $this->terbilang(floor($number / 1000000)).' juta '.$this->terbilang($number % 1000000);
        }

        return 'sejuta';
    }
}
