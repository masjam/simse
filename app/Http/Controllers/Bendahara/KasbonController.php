<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\Advance;
use App\Services\Payroll\AdvanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class KasbonController extends Controller
{
    public function __construct(
        private readonly AdvanceService $advanceService,
    ) {}

    public function index(Request $request): Response
    {
        $statusFilter = (string) $request->query('status', '');

        $query = Advance::with(['user', 'installments', 'approver'])->latest();

        if ($statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        $advances = $query->get()->map(fn (Advance $a): array => [
            'id' => $a->id,
            'employee_name' => $a->user?->name ?? 'Pegawai',
            'employee_email' => $a->user?->email,
            'amount' => (float) $a->amount,
            'installment_count' => $a->installment_count,
            'monthly_amount' => (float) $a->monthly_amount,
            'reason' => $a->reason,
            'status' => $a->status,
            'approved_by' => $a->approver?->name,
            'approved_at' => $a->approved_at?->translatedFormat('d M Y, H:i'),
            'rejection_reason' => $a->rejection_reason,
            'created_at' => $a->created_at?->translatedFormat('d M Y, H:i'),
            'installments' => $a->installments->map(fn ($ins): array => [
                'id' => $ins->id,
                'number' => $ins->installment_number,
                'amount' => (float) $ins->amount,
                'period' => "{$ins->month} {$ins->year}",
                'status' => $ins->status,
                'paid_at' => $ins->paid_at?->translatedFormat('d M Y'),
            ])->values(),
            'paid_count' => $a->installments->where('status', 'Lunas')->count(),
            'remaining_amount' => (float) $a->installments->where('status', 'Pending')->sum('amount'),
        ])->values();

        $allAdvances = Advance::with('installments')->get();
        $summary = [
            'pending_count' => $allAdvances->where('status', 'Diajukan')->count(),
            'active_count' => $allAdvances->where('status', 'Disetujui')->count(),
            'total_disbursed' => (float) $allAdvances->whereIn('status', ['Disetujui', 'Lunas'])->sum('amount'),
            'total_outstanding' => (float) $allAdvances->where('status', 'Disetujui')->sum(fn ($a) => $a->installments->where('status', 'Pending')->sum('amount')),
        ];

        return Inertia::render('Bendahara/Kasbon', [
            'advances' => $advances,
            'summary' => $summary,
            'selectedStatus' => $statusFilter,
        ]);
    }

    public function approve(Request $request, Advance $advance): RedirectResponse
    {
        try {
            $this->advanceService->approveAdvance($advance, $request->user());

            return redirect()->back()->with('success', "Pengajuan kasbon {$advance->user?->name} sebesar Rp ".number_format($advance->amount, 0, ',', '.').' telah disetujui.');
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, Advance $advance): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'reason.required' => 'Alasan penolakan kasbon wajib diisi.',
        ]);

        try {
            $this->advanceService->rejectAdvance($advance, $request->user(), $validated['reason']);

            return redirect()->back()->with('success', "Pengajuan kasbon {$advance->user?->name} telah ditolak.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
