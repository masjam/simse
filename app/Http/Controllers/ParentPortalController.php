<?php

namespace App\Http\Controllers;

use App\Http\Requests\Parent\UpdateWalletDailyLimitRequest;
use App\Http\Requests\ParentPortal\StoreParentAchievementRequest;
use App\Http\Requests\ParentPortal\SwitchChildRequest;
use App\Models\Bill;
use App\Models\CounselingSession;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudentViolation;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\StudentAcademicSummaryService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ParentPortalController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly StudentAcademicSummaryService $academicSummary,
    ) {}

    public function dashboard(Request $request): Response
    {
        [$children, $activeChild] = $this->resolveChildrenAndActive($request);

        $gradesData = $this->academicSummary->gradesAndRaport($activeChild);
        $attendanceData = $this->academicSummary->attendanceSummary($activeChild);
        $schedules = $this->academicSummary->classSchedules($activeChild);
        $weeklyJournals = $this->academicSummary->weeklyJournals($activeChild);

        $financeData = $this->buildFinanceData($activeChild);
        $behaviorData = $this->buildBehaviorData($activeChild);
        $achievementData = $this->buildAchievementData($activeChild);

        $raportSubjects = $gradesData['raportSubjects'];
        $averageScore = $raportSubjects->isNotEmpty()
            ? round((float) $raportSubjects->avg('nilai_akhir'), 1)
            : 0.0;

        $attendanceSummary = $attendanceData['summary'];
        $attendanceTotal = max(1, $attendanceSummary['total']);
        $attendancePct = $attendanceSummary['total'] > 0
            ? (int) round((($attendanceSummary['hadir'] + $attendanceSummary['terlambat']) / $attendanceTotal) * 100)
            : 100;

        $allowedTabs = ['akademik', 'presensi', 'keuangan', 'perilaku'];
        $requestedTab = (string) $request->query('tab', 'akademik');
        $activeTab = in_array($requestedTab, $allowedTabs, true) ? $requestedTab : 'akademik';

        return Inertia::render('Parent/Dashboard', [
            'children' => $children->map(fn (Student $child): array => $this->formatChildCard($child))->values(),
            'activeChild' => $this->formatChildCard($activeChild),
            'activeTab' => $activeTab,
            'overview' => [
                'average_score' => $averageScore,
                'attendance_pct' => $attendancePct,
                'unpaid_bills_count' => $financeData['unpaid_count'],
                'unpaid_bills_total' => $financeData['total_due_unpaid'],
                'violation_points' => $behaviorData['total_points'],
                'sp_status' => $behaviorData['sp_status'],
            ],
            'akademik' => [
                'raportSubjects' => $raportSubjects,
                'grades' => $gradesData['detailedGrades'],
                'schedules' => $schedules,
                'weeklyJournals' => $weeklyJournals,
                'achievements' => $this->academicSummary->approvedAchievements($activeChild),
            ],
            'presensi' => [
                'summary' => $attendanceSummary,
                'records' => $attendanceData['attendances'],
            ],
            'keuangan' => $financeData,
            'perilaku' => $behaviorData,
            'prestasi' => $achievementData,
        ]);
    }

    public function storeAchievement(StoreParentAchievementRequest $request): RedirectResponse
    {
        $student = Student::findOrFail($request->validated('student_id'));
        $this->authorize('viewAsParent', $student);

        $certificatePath = null;
        $certificateOriginalName = null;

        if ($request->hasFile('certificate')) {
            $file = $request->file('certificate');
            $certificatePath = Storage::disk('local')->putFile("achievements/{$student->id}", $file);
            $certificateOriginalName = $file->getClientOriginalName();
        }

        StudentAchievement::create([
            'student_id' => $student->id,
            'name' => $request->validated('name'),
            'category' => $request->validated('category'),
            'level' => $request->validated('level'),
            'year' => (int) $request->validated('year'),
            'organizer' => $request->validated('organizer'),
            'achievement_date' => $request->validated('achievement_date'),
            'description' => $request->validated('description'),
            'certificate_path' => $certificatePath,
            'certificate_original_name' => $certificateOriginalName,
            'status' => 'Menunggu Verifikasi',
            'submitted_by' => Auth::id(),
        ]);

        return redirect()->route('ortu.dashboard', ['tab' => 'akademik'])
            ->with('success', 'Pengajuan prestasi berhasil dikirim dan menunggu verifikasi Guru BK / TU / Bidang Lomba.');
    }

    public function downloadAchievementCertificate(StudentAchievement $achievement): BinaryFileResponse
    {
        $this->authorize('viewCertificate', $achievement);

        abort_if(
            ! $achievement->certificate_path || ! Storage::disk('local')->exists($achievement->certificate_path),
            404,
            'Berkas sertifikat tidak ditemukan.'
        );

        return response()->file(
            Storage::disk('local')->path($achievement->certificate_path)
        );
    }

    public function switchChild(SwitchChildRequest $request): RedirectResponse
    {
        $student = Student::findOrFail($request->validated('student_id'));
        $this->authorize('viewAsParent', $student);

        $request->session()->put('parent_active_student_id', $student->id);

        return redirect()->route('ortu.dashboard')
            ->with('success', 'Menampilkan data ananda '.($student->user?->name ?? 'Siswa').'.');
    }

    /**
     * @return array{0: Collection<int, Student>, 1: Student}
     */
    private function resolveChildrenAndActive(Request $request): array
    {
        $relations = [
            'user',
            'studentClass.tingkat',
            'studentClass.teacher.user',
            'studentClass.academicYear',
        ];

        $user = Auth::user();
        $children = Student::with($relations)
            ->where('parent_user_id', $user->id)
            ->get()
            ->sortBy(fn (Student $child): string => $child->user?->name ?? '')
            ->values();

        if ($children->isEmpty() && $user?->hasRole('Super Admin')) {
            $children = Student::with($relations)
                ->take(5)
                ->get()
                ->sortBy(fn (Student $child): string => $child->user?->name ?? '')
                ->values();
        }

        $requestedChildId = $request->query('child');

        if (is_string($requestedChildId) && $requestedChildId !== '') {
            $requestedChild = Student::with($relations)->findOrFail($requestedChildId);
            $this->authorize('viewAsParent', $requestedChild);
            $request->session()->put('parent_active_student_id', $requestedChild->id);

            if (! $children->contains('id', $requestedChild->id)) {
                $children->push($requestedChild);
            }

            return [$children, $requestedChild];
        }

        abort_if($children->isEmpty(), 403, 'Belum ada data siswa yang terhubung dengan akun orang tua ini.');

        $sessionChildId = $request->session()->get('parent_active_student_id');
        $activeChild = $sessionChildId
            ? ($children->firstWhere('id', $sessionChildId) ?? $children->first())
            : $children->first();

        $request->session()->put('parent_active_student_id', $activeChild->id);

        return [$children, $activeChild];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatChildCard(Student $student): array
    {
        return [
            'id' => $student->id,
            'name' => $student->user?->name ?? 'Siswa',
            'nisn' => $student->nisn ?? '-',
            'nis' => $student->nis ?? '-',
            'class_name' => $student->studentClass?->name ?? '-',
            'tingkat' => $student->studentClass?->tingkat?->name ?? ($student->studentClass?->level ?? '-'),
            'academic_year' => $student->studentClass?->academicYear?->name ?? '-',
            'semester' => $student->studentClass?->academicYear?->semester ?? '-',
            'wali_kelas' => $student->studentClass?->teacher?->user?->name ?? '-',
            'birth_date' => $student->tanggal_lahir ? Carbon::parse($student->tanggal_lahir)->format('d M Y') : '-',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFinanceData(Student $student): array
    {
        $bills = Bill::with(['billType', 'payments'])
            ->where('student_id', $student->id)
            ->orderByDesc('year')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Bill $bill): array {
                $verifiedPaid = (float) $bill->payments
                    ->where('status', 'Berhasil')
                    ->sum('amount_paid');
                $remaining = max(0.0, (float) $bill->amount_due - $verifiedPaid);

                return [
                    'id' => $bill->id,
                    'title' => $bill->billType?->name ?? 'Tagihan Sekolah',
                    'period' => trim(($bill->month ?? '').' '.($bill->year ?? '')),
                    'amount_due' => (float) $bill->amount_due,
                    'amount_paid' => $verifiedPaid,
                    'remaining' => $remaining,
                    'status' => $bill->status,
                ];
            })
            ->values();

        $payments = Payment::with(['bill.billType', 'cashier'])
            ->whereHas('bill', fn ($query) => $query->where('student_id', $student->id))
            ->orderByDesc('payment_date')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Payment $payment): array => [
                'id' => $payment->id,
                'bill_title' => $payment->bill?->billType?->name ?? 'Tagihan',
                'bill_period' => trim(($payment->bill?->month ?? '').' '.($payment->bill?->year ?? '')),
                'amount_paid' => (float) $payment->amount_paid,
                'payment_method' => $payment->payment_method,
                'payment_date' => $payment->payment_date?->format('d M Y') ?? '-',
                'status' => $payment->status,
                'cashier_name' => $payment->cashier?->name ?? 'Sistem',
            ])
            ->values();

        $unpaidBills = $bills->filter(fn (array $bill): bool => $bill['status'] !== 'Lunas')->values();

        $wallet = Wallet::firstOrCreate(
            ['student_id' => $student->id],
            ['balance' => 0]
        );
        $todayCanteenSpent = (float) WalletTransaction::where('wallet_id', $wallet->id)
            ->where('type', 'Pembayaran Kantin')
            ->where('status', 'Sukses')
            ->whereDate('created_at', today())
            ->sum('amount');

        return [
            'bills' => $bills,
            'unpaid_count' => $unpaidBills->count(),
            'total_due_unpaid' => (float) $unpaidBills->sum('remaining'),
            'total_paid_verified' => (float) $payments->where('status', 'Berhasil')->sum('amount_paid'),
            'pending_verification_count' => $payments->where('status', 'Menunggu Verifikasi')->count(),
            'payments' => $payments,
            'wallet' => [
                'balance' => (float) $wallet->balance,
                'has_pin' => ! empty($wallet->pin),
                'daily_limit' => $wallet->daily_limit !== null ? (float) $wallet->daily_limit : null,
                'today_spent' => $todayCanteenSpent,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildBehaviorData(Student $student): array
    {
        $violations = StudentViolation::with(['violationPoint', 'teacher.user'])
            ->where('student_id', $student->id)
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();

        $totalPoints = (int) $violations->sum(fn (StudentViolation $item): int => (int) ($item->violationPoint?->points ?? 0));

        $spStatus = 'Aman';
        if ($totalPoints >= 75) {
            $spStatus = 'SP 3';
        } elseif ($totalPoints >= 50) {
            $spStatus = 'SP 2';
        } elseif ($totalPoints >= 25) {
            $spStatus = 'SP 1';
        } elseif ($totalPoints > 0) {
            $spStatus = 'Pembinaan';
        }

        $counselingSessions = CounselingSession::with(['teacher.user'])
            ->where('student_id', $student->id)
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (CounselingSession $session): array => [
                'id' => $session->id,
                'type' => $session->type,
                'date' => $session->date?->format('d M Y') ?? '-',
                'teacher_name' => $session->teacher?->user?->name ?? 'Guru BK',
                'problem' => $session->problem,
                'solution' => $session->solution,
                'status' => $session->status,
            ])
            ->values();

        return [
            'total_points' => $totalPoints,
            'sp_status' => $spStatus,
            'violations' => $violations->map(fn (StudentViolation $violation): array => [
                'id' => $violation->id,
                'title' => $violation->violationPoint?->name ?? 'Catatan Pelanggaran',
                'category' => $violation->violationPoint?->category ?? 'Umum',
                'points' => (int) ($violation->violationPoint?->points ?? 0),
                'date' => $violation->date?->format('d M Y') ?? '-',
                'description' => $violation->description,
                'status' => $violation->status,
                'teacher_name' => $violation->teacher?->user?->name ?? 'Guru',
            ])->values(),
            'counseling_sessions' => $counselingSessions,
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function buildAchievementData(Student $student): Collection
    {
        return StudentAchievement::with('verifier')
            ->where('student_id', $student->id)
            ->orderByDesc('year')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (StudentAchievement $item): array => [
                'id' => $item->id,
                'name' => $item->name,
                'category' => $item->category ?? 'Umum',
                'level' => $item->level,
                'year' => $item->year,
                'organizer' => $item->organizer ?? '-',
                'achievement_date' => $item->achievement_date?->format('d M Y') ?? '-',
                'description' => $item->description,
                'status' => $item->status,
                'has_certificate' => (bool) $item->certificate_path,
                'certificate_name' => $item->certificate_original_name,
                'verifier_name' => $item->verifier?->name,
                'verified_at' => $item->verified_at?->format('d M Y, H:i'),
                'verification_notes' => $item->verification_notes,
            ])
            ->values();
    }

    public function updateWalletDailyLimit(UpdateWalletDailyLimitRequest $request): RedirectResponse
    {
        $student = Student::findOrFail($request->validated('student_id'));
        $this->authorize('viewAsParent', $student);

        $rawLimit = $request->validated('daily_limit');
        $limit = ($rawLimit === null || (float) $rawLimit <= 0) ? null : (float) $rawLimit;

        $wallet = Wallet::firstOrCreate(
            ['student_id' => $student->id],
            ['balance' => 0]
        );
        $wallet->update(['daily_limit' => $limit]);

        return redirect()
            ->route('ortu.dashboard', ['tab' => 'keuangan'])
            ->with('success', $limit === null
                ? 'Batas belanja harian kantin anak dinonaktifkan (tanpa batas).'
                : 'Batas belanja harian kantin anak berhasil diatur menjadi Rp '.number_format($limit, 0, ',', '.').'.'
            );
    }
}
