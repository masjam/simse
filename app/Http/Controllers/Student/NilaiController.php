<?php

namespace App\Http\Controllers\Student;

use App\Models\Grade;
use App\Services\StudentAcademicSummaryService;
use Inertia\Inertia;
use Inertia\Response;

class NilaiController extends BaseStudentController
{
    public function __construct(
        private readonly StudentAcademicSummaryService $academicSummary
    ) {}

    public function index(): Response
    {
        $student = $this->resolveStudent();

        $grades = Grade::with(['subject', 'teacher.user'])
            ->where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->get();

        $raportSubjects = $grades->groupBy('subject_id')->map(function ($subjectGrades): array {
            $first = $subjectGrades->first();
            $tugasValues = $subjectGrades->where('type', 'Tugas')->pluck('score');
            $kuisValues = $subjectGrades->where('type', 'Kuis')->pluck('score');
            $utsScore = $subjectGrades->where('type', 'UTS')->avg('score');
            $uasScore = $subjectGrades->where('type', 'UAS')->avg('score');

            $tugasAvg = $tugasValues->isNotEmpty() ? (float) $tugasValues->avg() : null;
            $kuisAvg = $kuisValues->isNotEmpty() ? (float) $kuisValues->avg() : null;

            $weightedParts = [];
            $weightTotal = 0.0;

            if ($tugasAvg !== null) {
                $weightedParts[] = $tugasAvg * 0.30;
                $weightTotal += 0.30;
            }
            if ($kuisAvg !== null) {
                $weightedParts[] = $kuisAvg * 0.20;
                $weightTotal += 0.20;
            }
            if ($utsScore !== null) {
                $weightedParts[] = (float) $utsScore * 0.25;
                $weightTotal += 0.25;
            }
            if ($uasScore !== null) {
                $weightedParts[] = (float) $uasScore * 0.25;
                $weightTotal += 0.25;
            }

            $finalScore = $weightTotal > 0 ? round(array_sum($weightedParts) / $weightTotal, 1) : 0.0;

            $predikat = 'B';
            $catatan = 'Menunjukkan pemahaman yang baik dan aktif dalam kegiatan pembelajaran.';
            if ($finalScore >= 90) {
                $predikat = 'A';
                $catatan = 'Sangat baik dalam memahami materi pembelajaran, berakhlak mulia, dan konsisten berprestasi.';
            } elseif ($finalScore >= 80) {
                $predikat = 'B+';
                $catatan = 'Baik dalam penguasaan materi, tekun mengerjakan tugas, dan santun kepada guru.';
            } elseif ($finalScore >= 70) {
                $predikat = 'C+';
                $catatan = 'Cukup memahami materi, perlu bimbingan tambahan dalam pemahaman konsep.';
            } elseif ($weightTotal > 0) {
                $predikat = 'D';
                $catatan = 'Perlu pendampingan intensif dalam pembelajaran dan pengulangan materi.';
            }

            return [
                'subject_id' => $first->subject_id,
                'subject_name' => $first->subject?->name ?? '-',
                'teacher_name' => $first->teacher?->user?->name ?? '-',
                'tugas_avg' => $tugasAvg !== null ? round($tugasAvg, 1) : null,
                'kuis_avg' => $kuisAvg !== null ? round($kuisAvg, 1) : null,
                'uts' => $utsScore !== null ? round((float) $utsScore, 1) : null,
                'uas' => $uasScore !== null ? round((float) $uasScore, 1) : null,
                'nilai_akhir' => $finalScore,
                'predikat' => $predikat,
                'catatan' => $catatan,
            ];
        })->values();

        $detailedGrades = $grades->map(fn (Grade $grade): array => [
            'id' => $grade->id,
            'subject' => $grade->subject?->name ?? '-',
            'teacher' => $grade->teacher?->user?->name ?? '-',
            'type' => $grade->type,
            'name' => $grade->name ?? $grade->type,
            'score' => (float) $grade->score,
            'notes' => $grade->notes,
            'date' => $grade->created_at?->format('d M Y') ?? '-',
        ])->values();

        return Inertia::render('Student/Nilai', [
            'student' => $this->formatStudentHeader($student),
            'raportSubjects' => $raportSubjects,
            'grades' => $detailedGrades,
            'achievements' => $this->academicSummary->approvedAchievements($student),
        ]);
    }
}
