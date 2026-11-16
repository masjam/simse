<?php

namespace App\Exports;

use App\Models\Exam;
use App\Services\Exam\ExamGrader;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExamItemAnalysisSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(
        protected Exam $exam
    ) {}

    public function title(): string
    {
        return 'Analisis Butir Soal';
    }

    public function headings(): array
    {
        return [
            'No Soal',
            'Tipe Soal',
            'Teks Soal',
            'Bobot',
            'Total Peserta Selesai',
            'Jumlah Benar',
            'Tingkat Kesulitan (%)',
            'Kategori Kesulitan',
            'Pilihan / Distraktor Terbanyak',
        ];
    }

    public function collection(): Collection
    {
        $this->exam->loadMissing([
            'questions.options',
            'attempts',
        ]);

        $finishedAttempts = $this->exam->attempts->whereNotNull('finished_at');
        $totalParticipants = $finishedAttempts->count();

        $graderService = app(ExamGrader::class);
        $data = [];
        $no = 1;

        foreach ($this->exam->questions as $question) {
            $grader = $graderService->getGrader($question->type);
            $correctCount = 0;
            $distractorCounts = [];

            if ($totalParticipants > 0) {
                foreach ($finishedAttempts as $attempt) {
                    $answers = $attempt->answers ?? [];
                    $qAnswer = $answers[$question->id] ?? null;

                    $result = $grader->grade($question, $qAnswer, $this->exam, [
                        'manual_essay_scores' => $attempt->manual_essay_scores ?? [],
                    ]);

                    if ($result['is_correct']) {
                        $correctCount++;
                    }

                    // Tally chosen option if available
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

            // Distractor summary
            $distractorSummary = '-';
            if (! empty($distractorCounts)) {
                arsort($distractorCounts);
                $topOptionId = array_key_first($distractorCounts);
                $topOpt = $question->options->firstWhere('id', $topOptionId);
                if ($topOpt) {
                    $pct = round(($distractorCounts[$topOptionId] / $totalParticipants) * 100);
                    $distractorSummary = "Opsi '{$topOpt->content}' ({$pct}% pemilih)";
                }
            }

            $data[] = [
                'no' => $no++,
                'type' => $question->type,
                'content' => strip_tags($question->content),
                'marks' => $question->marks,
                'participants' => $totalParticipants,
                'correct' => $correctCount,
                'difficulty_pct' => $diffPercent.'%',
                'category' => $category,
                'distractor' => $distractorSummary,
            ];
        }

        return collect($data);
    }
}
