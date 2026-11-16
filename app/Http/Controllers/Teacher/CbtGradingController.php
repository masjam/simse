<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Services\Exam\ExamGrader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CbtGradingController extends BaseTeacherController
{
    public function index(Exam $exam): Response
    {
        $exam->loadMissing([
            'subject',
            'questions' => fn ($q) => $q->where('type', 'Esai'),
            'attempts.student.user',
            'attempts.student.studentClass',
        ]);

        $essayQuestions = $exam->questions->map(fn ($q) => [
            'id' => $q->id,
            'content' => $q->content,
            'marks' => $q->marks,
        ]);

        // Filter finished attempts
        $attemptsData = $exam->attempts
            ->whereNotNull('finished_at')
            ->values()
            ->map(function (ExamAttempt $att) use ($essayQuestions) {
                $answers = $att->answers ?? [];
                $manualScores = $att->manual_essay_scores ?? [];

                $essays = [];
                $pendingCount = 0;

                foreach ($essayQuestions as $q) {
                    $qId = $q['id'];
                    $studentAnswer = $answers[$qId]['text_answer'] ?? '-';
                    $scoreInfo = $manualScores[$qId] ?? null;

                    $isGraded = $scoreInfo !== null;
                    if (! $isGraded) {
                        $pendingCount++;
                    }

                    $essays[] = [
                        'question_id' => $qId,
                        'question_content' => $q['content'],
                        'max_marks' => $q['marks'],
                        'student_answer' => $studentAnswer,
                        'score' => is_array($scoreInfo) ? ($scoreInfo['score'] ?? 0) : $scoreInfo,
                        'comment' => is_array($scoreInfo) ? ($scoreInfo['comment'] ?? null) : null,
                        'is_graded' => $isGraded,
                    ];
                }

                return [
                    'attempt_id' => $att->id,
                    'student_id' => $att->student_id,
                    'nis' => $att->student?->nis ?? '-',
                    'name' => $att->student?->user?->name ?? 'Siswa',
                    'class' => $att->student?->studentClass?->name ?? '-',
                    'total_score' => $att->score,
                    'needs_manual_grading' => $att->needs_manual_grading,
                    'pending_count' => $pendingCount,
                    'essays' => $essays,
                ];
            });

        return Inertia::render('Teacher/CBT/Grading', [
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'subject' => $exam->subject?->name ?? '-',
                'essay_count' => $essayQuestions->count(),
            ],
            'attempts' => $attemptsData,
        ]);
    }

    public function storeScore(Request $request, ExamAttempt $attempt): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'question_id' => 'required|uuid|exists:questions,id',
            'score' => 'required|numeric|min:0',
            'comment' => 'nullable|string|max:500',
        ]);

        $question = Question::findOrFail($validated['question_id']);
        if ($validated['score'] > $question->marks) {
            return response()->json([
                'message' => "Skor tidak boleh melebihi bobot maksimum soal ({$question->marks}).",
            ], 422);
        }

        $manualScores = $attempt->manual_essay_scores ?? [];
        $manualScores[$question->id] = [
            'score' => (float) $validated['score'],
            'comment' => $validated['comment'] ?? null,
            'graded_at' => now()->toIso8601String(),
        ];

        $attempt->manual_essay_scores = $manualScores;
        $attempt->save();

        // Recalculate full exam score using ExamGrader and sync to Grade
        $grader = app(ExamGrader::class);
        $grader->finalizeAttempt($attempt, $attempt->is_forced_submit);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Nilai esai berhasil disimpan dan total nilai ujian diperbarui.',
                'new_total_score' => $attempt->fresh()->score,
                'needs_manual_grading' => $attempt->fresh()->needs_manual_grading,
            ]);
        }

        return redirect()->back()->with('success', 'Nilai esai berhasil disimpan.');
    }
}
