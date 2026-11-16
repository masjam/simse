<?php

namespace App\Services\Exam\Graders;

use App\Models\Exam;
use App\Models\Question;
use App\Services\Exam\Contracts\QuestionGraderInterface;

class GradesGandaKompleks implements QuestionGraderInterface
{
    public function grade(Question $question, mixed $answer, ?Exam $exam = null, array $context = []): array
    {
        $maxMarks = (float) ($question->marks ?: 10);
        $scoringRule = $exam?->scoring_rule ?? 'standard';

        $selectedOptionIds = [];
        if (is_array($answer)) {
            if (isset($answer['option_ids']) && is_array($answer['option_ids'])) {
                $selectedOptionIds = $answer['option_ids'];
            } elseif (isset($answer['option_id'])) {
                $selectedOptionIds = (array) $answer['option_id'];
            } else {
                $selectedOptionIds = array_filter(array_values($answer), fn ($v) => is_string($v) || is_numeric($v));
            }
        } elseif (is_string($answer) && $answer !== '') {
            $selectedOptionIds = [$answer];
        }

        $selectedOptionIds = array_values(array_unique(array_filter(array_map('strval', $selectedOptionIds))));

        $correctOptionIds = $question->options
            ->where('is_correct', true)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();

        $totalCorrect = count($correctOptionIds);
        if ($totalCorrect === 0) {
            return [
                'earned_marks' => 0.0,
                'max_marks' => $maxMarks,
                'is_correct' => false,
                'needs_manual' => false,
                'feedback' => 'Kunci jawaban belum disetel',
            ];
        }

        if (empty($selectedOptionIds)) {
            return [
                'earned_marks' => 0.0,
                'max_marks' => $maxMarks,
                'is_correct' => false,
                'needs_manual' => false,
                'feedback' => 'Tidak dijawab',
            ];
        }

        if ($scoringRule === 'partial') {
            $studentCorrectCount = count(array_intersect($selectedOptionIds, $correctOptionIds));
            $studentWrongCount = count(array_diff($selectedOptionIds, $correctOptionIds));

            $ratio = max(0.0, ($studentCorrectCount - $studentWrongCount) / $totalCorrect);
            $earned = round($ratio * $maxMarks, 2);
            $isCorrect = abs($earned - $maxMarks) < 0.001;

            return [
                'earned_marks' => $earned,
                'max_marks' => $maxMarks,
                'is_correct' => $isCorrect,
                'needs_manual' => false,
                'feedback' => "Skor proporsional: {$studentCorrectCount} benar, {$studentWrongCount} salah dari {$totalCorrect} kunci",
            ];
        }

        // Standard: exact match of all correct options with no incorrect options
        $diff1 = array_diff($correctOptionIds, $selectedOptionIds);
        $diff2 = array_diff($selectedOptionIds, $correctOptionIds);
        $isExact = empty($diff1) && empty($diff2);

        return [
            'earned_marks' => $isExact ? $maxMarks : 0.0,
            'max_marks' => $maxMarks,
            'is_correct' => $isExact,
            'needs_manual' => false,
            'feedback' => $isExact ? 'Semua pilihan benar tepat' : 'Pilihan tidak sepenuhnya sesuai',
        ];
    }
}
