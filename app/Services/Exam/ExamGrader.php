<?php

namespace App\Services\Exam;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Grade;
use App\Services\Exam\Contracts\QuestionGraderInterface;
use App\Services\Exam\Graders\GradesBenarSalah;
use App\Services\Exam\Graders\GradesEsai;
use App\Services\Exam\Graders\GradesGandaKompleks;
use App\Services\Exam\Graders\GradesIsianRumpang;
use App\Services\Exam\Graders\GradesMenjodohkan;
use App\Services\Exam\Graders\GradesPilihanGanda;
use InvalidArgumentException;

class ExamGrader
{
    /**
     * @var array<string, QuestionGraderInterface>
     */
    protected array $graders = [];

    public function __construct()
    {
        $this->registerDefaultGraders();
    }

    protected function registerDefaultGraders(): void
    {
        $this->graders['Pilihan Ganda'] = new GradesPilihanGanda;
        $this->graders['Ganda Kompleks'] = new GradesGandaKompleks;
        $this->graders['Benar Salah'] = new GradesBenarSalah;
        $this->graders['Menjodohkan'] = new GradesMenjodohkan;
        $this->graders['Isian Rumpang'] = new GradesIsianRumpang;
        $this->graders['Esai'] = new GradesEsai;
    }

    public function getGrader(string $type): QuestionGraderInterface
    {
        if (! isset($this->graders[$type])) {
            throw new InvalidArgumentException("Grader untuk tipe soal [{$type}] belum didukung.");
        }

        return $this->graders[$type];
    }

    /**
     * Grade an entire ExamAttempt.
     *
     * @return array{score: float, earned_marks: float, total_marks: float, needs_manual_grading: bool, breakdown: array<string, array>}
     */
    public function gradeAttempt(ExamAttempt $attempt): array
    {
        $exam = $attempt->exam;
        if (! $exam->relationLoaded('questions')) {
            $exam->load('questions.options');
        }

        $answers = $attempt->answers ?? [];
        $manualScores = $attempt->manual_essay_scores ?? [];

        $context = [
            'attempt_id' => $attempt->id,
            'manual_essay_scores' => $manualScores,
        ];

        $totalMarks = 0.0;
        $earnedMarks = 0.0;
        $needsManualGrading = false;
        $breakdown = [];

        foreach ($exam->questions as $question) {
            $grader = $this->getGrader($question->type);
            $qAnswer = $answers[$question->id] ?? null;

            $result = $grader->grade($question, $qAnswer, $exam, $context);

            $totalMarks += $result['max_marks'];
            $earnedMarks += $result['earned_marks'];

            if ($result['needs_manual']) {
                $needsManualGrading = true;
            }

            $breakdown[$question->id] = $result;
        }

        $finalScore = $totalMarks > 0 ? round(($earnedMarks / $totalMarks) * 100, 2) : 0.0;

        return [
            'score' => $finalScore,
            'earned_marks' => round($earnedMarks, 2),
            'total_marks' => round($totalMarks, 2),
            'needs_manual_grading' => $needsManualGrading,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Grade and persist result directly into ExamAttempt, and optionally sync to Grade.
     */
    public function finalizeAttempt(ExamAttempt $attempt, bool $isForced = false): ExamAttempt
    {
        $result = $this->gradeAttempt($attempt);

        $attempt->update([
            'finished_at' => $attempt->finished_at ?? now(),
            'is_forced_submit' => $isForced || $attempt->is_forced_submit,
            'score' => $result['score'],
            'needs_manual_grading' => $result['needs_manual_grading'],
        ]);

        // Sync to Grade model if the exam has a subject and student is associated
        $this->syncToGradeTable($attempt);

        return $attempt;
    }

    /**
     * Synchronize final exam score to student's academic Grade record.
     */
    public function syncToGradeTable(ExamAttempt $attempt): void
    {
        $exam = $attempt->exam;
        $student = $attempt->student;

        if (! $exam || ! $student || ! $student->student_class_id) {
            return;
        }

        // Check if grade record already exists for this exam or create one
        Grade::updateOrCreate(
            [
                'student_id' => $student->id,
                'subject_id' => $exam->subject_id,
                'name' => $exam->title,
            ],
            [
                'teacher_id' => $exam->teacher_id,
                'type' => 'Kuis',
                'score' => $attempt->score ?? 0,
                'notes' => "Nilai CBT: {$exam->title}",
            ]
        );
    }
}
