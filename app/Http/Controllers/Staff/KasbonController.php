<?php

namespace App\Http\Controllers\Staff;

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
        $user = $request->user();

        $advances = Advance::with(['installments', 'approver'])
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(fn (Advance $a): array => [
                'id' => $a->id,
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
            ])
            ->values();

        $activeAdvance = $advances->firstWhere('status', 'Disetujui');
        $hasPendingRequest = $advances->where('status', 'Diajukan')->isNotEmpty();

        return Inertia::render('Staff/Kasbon', [
            'advances' => $advances,
            'summary' => [
                'total_borrowed' => (float) $advances->whereIn('status', ['Disetujui', 'Lunas'])->sum('amount'),
                'total_remaining' => (float) $advances->sum('remaining_amount'),
                'can_request' => ! $activeAdvance && ! $hasPendingRequest,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:100000', 'max:20000000'],
            'installment_count' => ['required', 'integer', 'min:1', 'max:12'],
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'amount.min' => 'Nominal pengajuan kasbon minimal Rp 100.000.',
            'amount.max' => 'Nominal pengajuan kasbon maksimal Rp 20.000.000.',
            'installment_count.between' => 'Tenor angsuran antara 1 sampai 12 bulan.',
            'reason.required' => 'Keperluan kasbon wajib dijelaskan.',
        ]);

        try {
            $this->advanceService->requestAdvance(
                $request->user(),
                (float) $validated['amount'],
                (int) $validated['installment_count'],
                $validated['reason']
            );

            return redirect()->back()->with('success', 'Pengajuan kasbon berhasil dikirimkan ke bendahara.');
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
