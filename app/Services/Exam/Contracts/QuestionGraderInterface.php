<?php

namespace App\Services\Exam\Contracts;

use App\Models\Exam;
use App\Models\Question;

interface QuestionGraderInterface
{
    /**
     * Grade a student's answer for a single question.
     *
     * @param  mixed  $answer  The raw answer payload submitted by the student
     * @param  array  $context  Extra context such as attempt or manual scores
     * @return array{earned_marks: float, max_marks: float, is_correct: bool, needs_manual: bool, feedback: ?string}
     */
    public function grade(Question $question, mixed $answer, ?Exam $exam = null, array $context = []): array;
}
