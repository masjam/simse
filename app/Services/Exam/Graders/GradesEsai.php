<?php

namespace App\Services\Exam\Graders;

use App\Models\Exam;
use App\Models\Question;
use App\Services\Exam\Contracts\QuestionGraderInterface;

class GradesEsai implements QuestionGraderInterface
{
    public function grade(Question $question, mixed $answer, ?Exam $exam = null, array $context = []): array
    {
        $maxMarks = (float) ($question->marks ?: 10);
        $manualScores = $context['manual_essay_scores'] ?? [];

        // Check if manual score was already provided by teacher
        if (isset($manualScores[$question->id])) {
            $manualData = $manualScores[$question->id];
            $score = is_array($manualData) ? (float) ($manualData['score'] ?? 0) : (float) $manualData;
            $score = max(0.0, min($score, $maxMarks));
            $comment = is_array($manualData) ? ($manualData['comment'] ?? null) : null;

            return [
                'earned_marks' => $score,
                'max_marks' => $maxMarks,
                'is_correct' => abs($score - $maxMarks) < 0.001,
                'needs_manual' => false,
                'feedback' => $comment ?: 'Telah dinilai guru',
            ];
        }

        // Student essay text
        $studentText = is_array($answer) ? ($answer['text_answer'] ?? null) : $answer;
        $hasAnswered = ! empty(trim((string) $studentText));

        return [
            'earned_marks' => 0.0,
            'max_marks' => $maxMarks,
            'is_correct' => false,
            'needs_manual' => true,
            'feedback' => $hasAnswered ? 'Menunggu koreksi guru' : 'Tidak dijawab (kosong)',
        ];
    }
}
