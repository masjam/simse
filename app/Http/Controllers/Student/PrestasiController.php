<?php

namespace App\Http\Controllers\Student;

use App\Models\CounselingRequest;
use App\Models\CounselingSession;
use App\Models\StudentAchievement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PrestasiController extends BaseStudentController
{
    /**
     * Tampilkan riwayat prestasi, piagam penghargaan, dan catatan pembinaan positif siswa.
     * PENTING: Poin pelanggaran, catatan SP, dan rincian kasus kedisiplinan TIDAK ditampilkan ke siswa SD.
     */
    public function index(Request $request): Response
    {
        $student = $this->resolveStudent();
        $header = $this->formatStudentHeader($student);

        // 1. Prestasi & Piagam Positif yang telah diverifikasi
        $achievements = StudentAchievement::where('student_id', $student->id)
            ->whereIn('status', ['Disetujui', 'Terverifikasi'])
            ->latest('achievement_date')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'category' => $a->category,
                'level' => $a->level,
                'year' => $a->year,
                'organizer' => $a->organizer ?? 'SD Muhammadiyah Al Mujahidin',
                'achievement_date' => $a->achievement_date?->translatedFormat('d F Y') ?? (string) $a->year,
                'description' => $a->description,
                'certificate_url' => $a->certificate_path ? route('siswa.prestasi.pdf', $a->id) : null,
            ])
            ->all();

        // 2. Catatan Pembinaan Positif & Motivasi (Hanya sesi konseling yang bersifat positif/motivasi)
        $guidanceNotes = CounselingSession::with('teacher.user')
            ->where('student_id', $student->id)
            ->whereIn('type', ['Akademik', 'Karir', 'Pribadi', 'Kelompok'])
            ->latest('date')
            ->get()
            ->map(fn ($cs) => [
                'id' => $cs->id,
                'type' => $cs->type,
                'date' => $cs->date?->translatedFormat('d M Y') ?? '-',
                'counselor_name' => $cs->teacher?->user?->name ?? 'Guru Pembimbing BK',
                'positive_summary' => $cs->solution ?: ($cs->problem ?: 'Apresiasi semangat belajar dan keaktifan ananda di sekolah.'),
            ])
            ->all();

        // 3. Riwayat Permohonan Konseling yang diajukan oleh Siswa ("Hubungi Guru BK")
        $counselingRequests = CounselingRequest::where('student_id', $student->id)
            ->latest()
            ->get()
            ->map(fn ($cr) => [
                'id' => $cr->id,
                'category' => $cr->category,
                'preferred_schedule' => $cr->preferred_schedule ?? 'Kapan saja',
                'notes' => $cr->notes,
                'status' => $cr->status,
                'created_at' => $cr->created_at?->translatedFormat('d M Y, H:i') ?? '-',
                'scheduled_at' => $cr->scheduled_at?->translatedFormat('d M Y, H:i') ?? null,
                'counselor_feedback' => $cr->counselor_notes,
            ])
            ->all();

        return Inertia::render('Student/Prestasi', [
            'student' => $header,
            'achievements' => $achievements,
            'guidanceNotes' => $guidanceNotes,
            'counselingRequests' => $counselingRequests,
            'stats' => [
                'total_achievements' => count($achievements),
                'total_guidance' => count($guidanceNotes),
                'active_requests' => collect($counselingRequests)->whereIn('status', ['Diajukan', 'Dijadwalkan'])->count(),
            ],
        ]);
    }
}
