<?php

namespace App\Http\Controllers\Teacher;

use App\Exports\ExamAnalysisExport;
use App\Models\Exam;
use App\Services\Exam\ExamGrader;
use App\Services\Pdf\PdfRenderer;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CbtReportController extends BaseTeacherController
{
    public function __construct(
        protected PdfRenderer $pdfRenderer,
        protected ExamGrader $examGrader
    ) {}

    public function show(Exam $exam): Response
    {
        $exam->loadMissing([
            'subject',
            'teacher.user',
            'questions.options',
            'attempts.student.user',
            'attempts.student.studentClass',
            'attempts.violations',
        ]);

        $finishedAttempts = $exam->attempts->whereNotNull('finished_at');
        $scores = $finishedAttempts->pluck('score')->filter(fn ($s) => $s !== null)->values();

        $stats = [
            'total_participants' => $finishedAttempts->count(),
            'avg_score' => $scores->isNotEmpty() ? round($scores->avg(), 1) : 0,
            'max_score' => $scores->isNotEmpty() ? round($scores->max(), 1) : 0,
            'min_score' => $scores->isNotEmpty() ? round($scores->min(), 1) : 0,
            'distribution' => [
                'a' => $scores->filter(fn ($s) => $s >= 85)->count(),
                'b' => $scores->filter(fn ($s) => $s >= 70 && $s < 85)->count(),
                'c' => $scores->filter(fn ($s) => $s >= 55 && $s < 70)->count(),
                'd' => $scores->filter(fn ($s) => $s < 55)->count(),
            ],
        ];

        // Item Analysis
        $itemAnalysis = $this->calculateItemAnalysis($exam, $finishedAttempts);

        $studentsData = $finishedAttempts->sortByDesc('score')->values()->map(fn ($att) => [
            'attempt_id' => $att->id,
            'nis' => $att->student?->nis ?? '-',
            'name' => $att->student?->user?->name ?? 'Siswa',
            'class' => $att->student?->studentClass?->name ?? '-',
            'started_at' => $att->started_at?->format('d/m/Y H:i'),
            'finished_at' => $att->finished_at?->format('d/m/Y H:i'),
            'violations_count' => $att->violations->count(),
            'is_forced_submit' => $att->is_forced_submit,
            'score' => $att->score,
        ]);

        return Inertia::render('Teacher/CBT/Report', [
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'subject' => $exam->subject?->name ?? '-',
                'teacher' => $exam->teacher?->user?->name ?? '-',
                'duration_minutes' => $exam->duration_minutes,
                'start_time' => $exam->start_time->format('d/m/Y H:i'),
                'end_time' => $exam->end_time->format('d/m/Y H:i'),
            ],
            'stats' => $stats,
            'students' => $studentsData,
            'itemAnalysis' => $itemAnalysis,
        ]);
    }

    public function exportExcel(Exam $exam): BinaryFileResponse
    {
        $fileName = 'Analisis_CBT_'.str_replace(' ', '_', $exam->title).'_'.date('Ymd_His').'.xlsx';

        return Excel::download(new ExamAnalysisExport($exam), $fileName);
    }

    public function exportPdf(Exam $exam): HttpResponse
    {
        $exam->loadMissing([
            'subject',
            'teacher.user',
            'questions.options',
            'attempts.student.user',
            'attempts.student.studentClass',
            'attempts.violations',
        ]);

        $finishedAttempts = $exam->attempts->whereNotNull('finished_at');
        $scores = $finishedAttempts->pluck('score')->filter(fn ($s) => $s !== null)->values();

        $stats = [
            'total_participants' => $finishedAttempts->count(),
            'avg_score' => $scores->isNotEmpty() ? round($scores->avg(), 1) : 0,
            'max_score' => $scores->isNotEmpty() ? round($scores->max(), 1) : 0,
            'min_score' => $scores->isNotEmpty() ? round($scores->min(), 1) : 0,
        ];

        $itemAnalysis = $this->calculateItemAnalysis($exam, $finishedAttempts);

        $verification = $this->pdfRenderer->createVerification(
            documentType: 'Laporan Rekap CBT',
            recipientName: $exam->teacher?->user?->name ?? 'Guru',
            reference: $exam,
            metadata: [
                'title' => $exam->title,
                'subject' => $exam->subject?->name,
                'participants' => $stats['total_participants'],
                'avg_score' => $stats['avg_score'],
            ],
            title: "Rekap Nilai & Analisis Butir Soal CBT - {$exam->title}"
        );

        return $this->pdfRenderer->stream(
            view: 'pdf.cbt-recap',
            data: [
                'exam' => $exam,
                'stats' => $stats,
                'attempts' => $finishedAttempts->sortByDesc('score')->values(),
                'item_analysis' => $itemAnalysis,
                'academic_year' => '2026/2027',
            ],
            filename: "Rekap_CBT_{$exam->id}",
            orientation: 'portrait',
            paperSize: 'a4',
            verification: $verification
        );
    }

    protected function calculateItemAnalysis(Exam $exam, $finishedAttempts): array
    {
        $totalParticipants = $finishedAttempts->count();
        $result = [];

        foreach ($exam->questions as $idx => $question) {
            $grader = $this->examGrader->getGrader($question->type);
            $correctCount = 0;
            $distractorCounts = [];

            if ($totalParticipants > 0) {
                foreach ($finishedAttempts as $attempt) {
                    $answers = $attempt->answers ?? [];
                    $qAnswer = $answers[$question->id] ?? null;

                    $gradeRes = $grader->grade($question, $qAnswer, $exam, [
                        'manual_essay_scores' => $attempt->manual_essay_scores ?? [],
                    ]);

                    if ($gradeRes['is_correct']) {
                        $correctCount++;
                    }

                    $chosen = is_array($qAnswer) ? ($qAnswer['option_id'] ?? null) : $qAnswer;
                    if ($chosen) {
                        $distractorCounts[(string) $chosen] = ($distractorCounts[(string) $chosen] ?? 0) + 1;
                    }
                }
            }

            $diffPercent = $totalParticipants > 0 ? round(($correctCount / $totalParticipants) * 100, 1) : 0;
            $category = 'Belum Diuji';
            if ($totalParticipants > 0) {
                if ($diffPercent >= 70) {
                    $category = 'Mudah';
                } elseif ($diffPercent >= 30) {
                    $category = 'Sedang';
                } else {
                    $category = 'Sukar';
                }
            }

            // Distractor details
            $distractors = [];
            foreach ($question->options as $opt) {
                $count = $distractorCounts[(string) $opt->id] ?? 0;
                $pct = $totalParticipants > 0 ? round(($count / $totalParticipants) * 100) : 0;
                $distractors[] = [
                    'option_id' => $opt->id,
                    'content' => $opt->content,
                    'is_correct' => (bool) $opt->is_correct,
                    'chosen_count' => $count,
                    'percentage' => $pct,
                ];
            }

            $result[] = [
                'question_id' => $question->id,
                'number' => $idx + 1,
                'type' => $question->type,
                'content' => $question->content,
                'marks' => $question->marks,
                'correct_count' => $correctCount,
                'total_participants' => $totalParticipants,
                'difficulty_pct' => $diffPercent.'%',
                'category' => $category,
                'distractors' => $distractors,
            ];
        }

        return $result;
    }
}
