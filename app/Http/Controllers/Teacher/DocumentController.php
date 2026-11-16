<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentViolation;
use App\Services\Pdf\PdfRenderer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DocumentController extends Controller
{
    public function __construct(
        protected PdfRenderer $pdfRenderer
    ) {}

    /**
     * Download or stream student e-Raport as PDF.
     */
    public function raport(Request $request, Student $student): Response
    {
        $user = $request->user();
        // Otorisasi: Wali kelas, Guru terkait, Super Admin, atau Orang Tua dari siswa bersangkutan
        $isParentOwner = $user->hasRole('Orang Tua') && $student->parent_user_id === $user->id;
        if (! $user->hasRole('Super Admin') && ! $user->hasRole('Guru') && ! $user->hasRole('Guru Pengajar') && ! $user->hasRole('Guru BK') && ! $isParentOwner) {
            abort(403, 'Anda tidak memiliki hak akses mencetak raport ini.');
        }

        $student->loadMissing(['user', 'studentClass.teacher', 'grades.subject']);

        $gradesList = $student->grades->groupBy('subject_id')->map(function ($items) {
            $first = $items->first();
            $avgScore = $items->avg('score') ?: 85;
            $grade = 'B';
            if ($avgScore >= 90) {
                $grade = 'A';
            } elseif ($avgScore >= 80) {
                $grade = 'B+';
            } elseif ($avgScore >= 70) {
                $grade = 'C';
            } else {
                $grade = 'D';
            }

            return [
                'subject' => $first?->subject?->name ?? 'Mata Pelajaran',
                'kkm' => 75,
                'score' => round($avgScore, 1),
                'grade' => $grade,
                'notes' => $first?->notes ?? 'Menunjukkan penguasaan materi yang baik dan aktif dalam KBM.',
            ];
        })->values()->all();

        $verification = $this->pdfRenderer->createVerification(
            documentType: 'e-Raport',
            recipientName: $student->user?->name ?? 'Siswa',
            reference: $student,
            metadata: [
                'class' => $student->studentClass?->name,
                'semester' => $request->query('semester', 'Ganjil 2026/2027'),
            ],
            title: "e-Raport Semester {$request->query('semester', 'Ganjil 2026/2027')} - {$student->user?->name}"
        );

        $data = [
            'title' => "e-Raport - {$student->user?->name}",
            'student' => $student,
            'studentName' => $student->user?->name,
            'className' => $student->studentClass?->name ?? '1 Brilliant',
            'academicYear' => '2026/2027',
            'semester' => $request->query('semester', 'Ganjil'),
            'grades' => $gradesList,
            'attendanceSummary' => [
                'sakit' => 1,
                'izin' => 0,
                'alpa' => 0,
            ],
            'teacherName' => $student->studentClass?->teacher?->user?->name ?? $user->name,
            'teacherNip' => $student->studentClass?->teacher?->nip ?? 'NBM. 1092381',
        ];

        return $this->pdfRenderer->stream(
            view: 'pdf.raport',
            data: $data,
            filename: "e-Raport_{$student->nis}_{$student->user?->name}.pdf",
            orientation: 'portrait',
            verification: $verification
        );
    }

    /**
     * Download or stream Surat Peringatan (SP) as PDF.
     */
    public function suratPeringatan(Request $request, StudentViolation $violation): Response
    {
        $user = $request->user();
        if (! $user->hasRole('Super Admin') && ! $user->hasRole('Guru') && ! $user->hasRole('Guru Pengajar') && ! $user->hasRole('Guru BK')) {
            abort(403);
        }

        $violation->loadMissing(['student.user', 'student.studentClass', 'violationPoint']);
        $student = $violation->student;

        $verification = $this->pdfRenderer->createVerification(
            documentType: 'Surat Peringatan',
            recipientName: $student->user?->name ?? 'Siswa',
            reference: $violation,
            title: "Surat Peringatan Siswa - {$student->user?->name}"
        );

        $data = [
            'title' => "Surat Peringatan - {$student->user?->name}",
            'spLevel' => 'SP-1',
            'docNumber' => 'SP/BK/MAM/'.date('Y').'/'.substr($violation->id, 0, 4),
            'studentName' => $student->user?->name ?? 'Siswa',
            'studentNis' => $student->nis,
            'studentNisn' => $student->nisn,
            'className' => $student->studentClass?->name ?? '-',
            'points' => $violation->violationPoint?->points ?? 10,
            'violationDescription' => $violation->description ?: ($violation->violationPoint?->name ?? 'Pelanggaran tata tertib sekolah.'),
            'counselorName' => $user->name,
            'counselorNip' => 'NBM. 1092837',
        ];

        return $this->pdfRenderer->stream(
            view: 'pdf.surat-peringatan',
            data: $data,
            filename: "SP_{$student->user?->name}.pdf",
            verification: $verification
        );
    }

    /**
     * Download or stream Berita Acara Pelanggaran (BAP) as PDF.
     */
    public function bap(Request $request, StudentViolation $violation): Response
    {
        $user = $request->user();
        if (! $user->hasRole('Super Admin') && ! $user->hasRole('Guru') && ! $user->hasRole('Guru Pengajar') && ! $user->hasRole('Guru BK')) {
            abort(403);
        }

        $violation->loadMissing(['student.user', 'student.studentClass', 'violationPoint']);
        $student = $violation->student;

        $verification = $this->pdfRenderer->createVerification(
            documentType: 'Berita Acara Pelanggaran',
            recipientName: $student->user?->name ?? 'Siswa',
            reference: $violation,
            title: "Berita Acara Pembinaan - {$student->user?->name}"
        );

        $data = [
            'title' => "BAP Pembinaan - {$student->user?->name}",
            'docNumber' => 'BAP/BK/'.date('Ym').'/'.substr($violation->id, 0, 4),
            'studentName' => $student->user?->name ?? 'Siswa',
            'className' => $student->studentClass?->name ?? '-',
            'parentName' => $student->nama_ayah ?: ($student->nama_ibu ?: 'Orang Tua Siswa'),
            'chronology' => $violation->description ?: 'Melakukan pelanggaran tata tertib sekolah di lingkungan sekolah.',
            'clarification' => 'Siswa memberikan keterangan dan mengakui kekhilafan serta berjanji tidak mengulangi.',
            'commitment' => 'Siswa bersedia dibina secara berkala oleh guru BK dan wali kelas serta meningkatkan kedisiplinan ibadah.',
            'counselorName' => $user->name,
        ];

        return $this->pdfRenderer->stream(
            view: 'pdf.berita-acara-pelanggaran',
            data: $data,
            filename: "BAP_{$student->user?->name}.pdf",
            verification: $verification
        );
    }
}
