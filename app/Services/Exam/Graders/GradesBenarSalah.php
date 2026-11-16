<?php

namespace App\Services\Exam\Graders;

use App\Models\Exam;
use App\Models\Question;
use App\Services\Exam\Contracts\QuestionGraderInterface;

class GradesBenarSalah implements QuestionGraderInterface
{
    public function grade(Question $question, mixed $answer, ?Exam $exam = null, array $context = []): array
    {
        $maxMarks = (float) ($question->marks ?: 10);
        $selectedOptionId = is_array($answer) ? ($answer['option_id'] ?? null) : $answer;

        if (! $selectedOptionId) {
            return [
                'earned_marks' => 0.0,
                'max_marks' => $maxMarks,
                'is_correct' => false,
                'needs_manual' => false,
                'feedback' => 'Tidak dijawab',
            ];
        }

        $correctOption = $question->options->firstWhere('is_correct', true);
        $isCorrect = $correctOption && ((string) $correctOption->id === (string) $selectedOptionId);

        return [
            'earned_marks' => $isCorrect ? $maxMarks : 0.0,
            'max_marks' => $maxMarks,
            'is_correct' => $isCorrect,
            'needs_manual' => false,
            'feedback' => $isCorrect ? 'Pernyataan benar diidentifikasi tepat' : 'Identifikasi pernyataan salah',
        ];
    }
}
