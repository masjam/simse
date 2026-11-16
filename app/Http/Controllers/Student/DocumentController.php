<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Services\Pdf\PdfRenderer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DocumentController extends Controller
{
    public function __construct(
        protected PdfRenderer $pdfRenderer
    ) {}

    /**
     * Download or stream official KTS (Kartu Tanda Siswa) as PDF.
     */
    public function kts(Request $request): Response
    {
        $user = $request->user();
        $student = null;

        if ($user->hasRole('Siswa')) {
            $student = Student::with(['user', 'studentClass'])->where('user_id', $user->id)->first();
        } elseif ($user->hasRole('Orang Tua')) {
            $studentId = $request->query('student_id');
            $student = Student::with(['user', 'studentClass'])
                ->where('parent_user_id', $user->id)
                ->when($studentId, fn ($q) => $q->where('id', $studentId))
                ->first();
        } elseif ($user->hasRole('Super Admin')) {
            $studentId = $request->query('student_id');
            $student = Student::with(['user', 'studentClass'])
                ->when($studentId, fn ($q) => $q->where('id', $studentId))
                ->first();
        }

        if (! $student) {
            abort(404, 'Data siswa tidak ditemukan.');
        }

        $verification = $this->pdfRenderer->createVerification(
            documentType: 'Kartu Tanda Siswa',
            recipientName: $student->user?->name ?? 'Siswa',
            reference: $student,
            title: "Kartu Tanda Siswa - {$student->user?->name}"
        );

        $data = [
            'title' => "KTS - {$student->user?->name}",
            'student' => $student,
            'studentName' => $student->user?->name,
            'studentNis' => $student->nis ?? '202601001',
            'studentNisn' => $student->nisn ?? '0148923841',
            'className' => $student->studentClass?->name ?? '1 Brilliant',
        ];

        return $this->pdfRenderer->stream(
            view: 'pdf.kts',
            data: $data,
            filename: "KTS_{$student->nis}_{$student->user?->name}.pdf",
            verification: $verification
        );
    }

    /**
     * Download or stream student achievement certificate as PDF.
     */
    public function achievementCertificate(Request $request, StudentAchievement $achievement): Response
    {
        $user = $request->user();
        $achievement->loadMissing(['student.user', 'student.studentClass']);
        $student = $achievement->student;

        // Otorisasi: Siswa pemilik, Ortu pemilik, Guru, Super Admin
        $canAccess = $user->hasRole('Super Admin')
            || $user->hasRole('Guru')
            || ($user->hasRole('Siswa') && $student?->user_id === $user->id)
            || ($user->hasRole('Orang Tua') && $student?->parent_user_id === $user->id);

        if (! $canAccess) {
            abort(403, 'Anda tidak memiliki hak akses mengunduh sertifikat ini.');
        }

        $verification = $this->pdfRenderer->createVerification(
            documentType: 'Sertifikat Prestasi',
            recipientName: $student?->user?->name ?? 'Siswa',
            reference: $achievement,
            metadata: [
                'title' => $achievement->title,
                'year' => $achievement->year,
            ],
            title: "Piagam Penghargaan {$achievement->title} - {$student?->user?->name}"
        );

        $data = [
            'title' => "Piagam Penghargaan - {$student?->user?->name}",
            'certificateNumber' => 'SERT/PRESTASI/MAM/'.($achievement->year ?? date('Y')).'/'.substr($achievement->id, 0, 4),
            'studentName' => $student?->user?->name ?? 'Siswa',
            'studentNis' => $student?->nis ?? '-',
            'studentNisn' => $student?->nisn ?? '-',
            'className' => $student?->studentClass?->name ?? '-',
            'achievementTitle' => $achievement->title,
            'eventDescription' => $achievement->description ?: 'Kegiatan Kejuaraan & Pembinaan Bakat Siswa',
            'year' => $achievement->year ?? date('Y'),
        ];

        return $this->pdfRenderer->stream(
            view: 'pdf.sertifikat-prestasi',
            data: $data,
            filename: "Sertifikat_{$student?->user?->name}_{$achievement->id}.pdf",
            orientation: 'landscape',
            verification: $verification
        );
    }
}
