<?php

namespace App\Services\Exam\Graders;

use App\Models\Exam;
use App\Models\Question;
use App\Services\Exam\Contracts\QuestionGraderInterface;

class GradesMenjodohkan implements QuestionGraderInterface
{
    public function grade(Question $question, mixed $answer, ?Exam $exam = null, array $context = []): array
    {
        $maxMarks = (float) ($question->marks ?: 10);

        // Normalize student answer pairs
        $studentPairs = [];
        if (is_array($answer)) {
            if (isset($answer['pairs']) && is_array($answer['pairs'])) {
                $studentPairs = $answer['pairs'];
            } elseif (isset($answer['matches']) && is_array($answer['matches'])) {
                $studentPairs = $answer['matches'];
            } else {
                $studentPairs = $answer;
            }
        }

        // Options hold premise (content) and correct pair (pair_match)
        $optionsWithPairs = $question->options->filter(fn ($o) => ! empty($o->pair_match));
        $totalPairs = $optionsWithPairs->count();

        if ($totalPairs === 0) {
            return [
                'earned_marks' => 0.0,
                'max_marks' => $maxMarks,
                'is_correct' => false,
                'needs_manual' => false,
                'feedback' => 'Belum ada kunci pasangan pada soal ini',
            ];
        }

        if (empty($studentPairs)) {
            return [
                'earned_marks' => 0.0,
                'max_marks' => $maxMarks,
                'is_correct' => false,
                'needs_manual' => false,
                'feedback' => 'Tidak ada pasangan yang dijodohkan',
            ];
        }

        $correctCount = 0;
        foreach ($optionsWithPairs as $opt) {
            $studentMatch = $studentPairs[$opt->id] ?? $studentPairs[(string) $opt->id] ?? null;

            if ($studentMatch !== null) {
                $expected = trim((string) $opt->pair_match);
                $given = trim((string) $studentMatch);

                if (strcasecmp($expected, $given) === 0) {
                    $correctCount++;
                }
            }
        }

        $earned = round(($correctCount / $totalPairs) * $maxMarks, 2);
        $isExact = ($correctCount === $totalPairs);

        return [
            'earned_marks' => $earned,
            'max_marks' => $maxMarks,
            'is_correct' => $isExact,
            'needs_manual' => false,
            'feedback' => "Berhasil menjodohkan {$correctCount} dari {$totalPairs} pasangan dengan benar",
        ];
    }
}
