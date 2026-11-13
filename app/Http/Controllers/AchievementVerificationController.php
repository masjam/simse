<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyStudentAchievementRequest;
use App\Models\StudentAchievement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AchievementVerificationController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $statusFilter = (string) $request->query('status', 'Menunggu Verifikasi');
        $allowedFilters = ['Menunggu Verifikasi', 'Disetujui', 'Ditolak', 'Semua'];

        if (! in_array($statusFilter, $allowedFilters, true)) {
            $statusFilter = 'Menunggu Verifikasi';
        }

        $query = StudentAchievement::with([
            'student.user',
            'student.studentClass',
            'submitter',
            'verifier',
        ])
            ->orderByRaw("CASE WHEN status = 'Menunggu Verifikasi' THEN 0 WHEN status = 'Disetujui' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at');

        if ($statusFilter !== 'Semua') {
            $query->where('status', $statusFilter);
        }

        $achievements = $query->get()->map(fn (StudentAchievement $item): array => [
            'id' => $item->id,
            'student_name' => $item->student?->user?->name ?? 'Siswa',
            'student_nisn' => $item->student?->nisn ?? '-',
            'class_name' => $item->student?->studentClass?->name ?? '-',
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
            'submitter_name' => $item->submitter?->name ?? 'Admin / Sekolah',
            'verifier_name' => $item->verifier?->name,
            'verified_at' => $item->verified_at?->format('d M Y, H:i'),
            'verification_notes' => $item->verification_notes,
            'created_at' => $item->created_at?->format('d M Y, H:i') ?? '-',
        ])->values();

        $counts = [
            'pending' => StudentAchievement::where('status', 'Menunggu Verifikasi')->count(),
            'approved' => StudentAchievement::where('status', 'Disetujui')->count(),
            'rejected' => StudentAchievement::where('status', 'Ditolak')->count(),
        ];

        return Inertia::render('Achievements/Verification', [
            'achievements' => $achievements,
            'statusFilter' => $statusFilter,
            'counts' => $counts,
        ]);
    }

    public function verify(VerifyStudentAchievementRequest $request, StudentAchievement $achievement): RedirectResponse
    {
        $this->authorize('verify', $achievement);

        $achievement->update([
            'status' => $request->validated('status'),
            'verification_notes' => $request->validated('verification_notes'),
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        $message = $request->validated('status') === 'Disetujui'
            ? 'Prestasi siswa disetujui dan akan tampil pada catatan e-Raport.'
            : 'Pengajuan prestasi siswa ditolak.';

        return back()->with('success', $message);
    }

    public function downloadCertificate(StudentAchievement $achievement): BinaryFileResponse
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
}
