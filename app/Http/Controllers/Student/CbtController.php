<?php

namespace App\Http\Controllers\Student;

use App\Http\Requests\Student\RecordExamViolationRequest;
use App\Http\Requests\Student\SaveExamAnswerRequest;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamViolation;
use App\Models\Option;
use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CbtController extends BaseStudentController
{
    public function index(): Response
    {
        $student = $this->resolveStudent();
        $now = Carbon::now();

        $exams = Exam::with([
            'subject',
            'teacher.user',
            'questions',
            'attempts' => fn ($query) => $query->where('student_id', $student->id)->withCount('violations'),
        ])
            ->where(function ($query) use ($student): void {
                $query->where('is_active', true)
                    ->orWhereHas('attempts', fn ($q) => $q->where('student_id', $student->id));
            })
            ->orderByDesc('start_time')
            ->get()
            ->map(function (Exam $exam) use ($now): array {
                $attempt = $exam->attempts->first();
                $status = 'Berakhir';
                $canEnter = false;

                if ($attempt?->finished_at) {
                    $status = 'Selesai';
                } elseif ($attempt && ! $attempt->finished_at) {
                    $deadline = $this->calculateAttemptDeadline($exam, $attempt);
                    if ($now->lt($deadline)) {
                        $status = 'Sedang Dikerjakan';
                        $canEnter = true;
                    } else {
                        $status = 'Selesai';
                    }
                } elseif ($exam->is_active && $now->between($exam->start_time, $exam->end_time)) {
                    $status = 'Tersedia';
                    $canEnter = $exam->questions->isNotEmpty();
                } elseif ($exam->is_active && $now->lt($exam->start_time)) {
                    $status = 'Akan Datang';
                }

                return [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'description' => $exam->description,
                    'subject' => $exam->subject?->name ?? '-',
                    'teacher' => $exam->teacher?->user?->name ?? '-',
                    'start_time' => $exam->start_time->format('d M Y, H:i'),
                    'end_time' => $exam->end_time->format('d M Y, H:i'),
                    'duration_minutes' => $exam->duration_minutes,
                    'max_violations' => $exam->max_violations,
                    'questions_count' => $exam->questions->count(),
                    'status' => $status,
                    'can_enter' => $canEnter,
                    'attempt' => $attempt ? [
                        'id' => $attempt->id,
                        'started_at' => $attempt->started_at?->format('d M Y, H:i'),
                        'finished_at' => $attempt->finished_at?->format('d M Y, H:i'),
                        'score' => $attempt->score,
                        'is_forced_submit' => $attempt->is_forced_submit,
                        'violations_count' => $attempt->violations_count ?? 0,
                    ] : null,
                ];
            })->values();

        return Inertia::render('Student/CBT', [
            'student' => $this->formatStudentHeader($student),
            'exams' => $exams,
        ]);
    }

    public function startExam(Exam $exam): Response|RedirectResponse
    {
        $student = $this->resolveStudent();
        $now = Carbon::now();

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        if (! $attempt) {
            if (! $exam->is_active || ! $now->between($exam->start_time, $exam->end_time)) {
                return redirect()->route('siswa.cbt')->with('error', 'Ujian tidak sedang dibuka atau sudah berakhir.');
            }

            if ($exam->questions()->count() === 0) {
                return redirect()->route('siswa.cbt')->with('error', 'Soal ujian belum tersedia.');
            }

            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'answers' => [],
                'started_at' => $now,
            ]);
        } else {
            Gate::authorize('update', $attempt);
        }

        if ($attempt->finished_at === null) {
            $deadline = $this->calculateAttemptDeadline($exam, $attempt);
            if ($now->greaterThanOrEqualTo($deadline)) {
                $this->finalizeExamAttempt($exam, $attempt, true);
                $attempt->refresh();
            }
        }

        $exam->load(['subject', 'teacher.user', 'questions.options']);
        $attempt->loadCount('violations');

        $remainingSeconds = $attempt->finished_at
            ? 0
            : max(0, (int) $now->diffInSeconds($this->calculateAttemptDeadline($exam, $attempt), false));

        $questions = $exam->questions->map(function (Question $question): array {
            return [
                'id' => $question->id,
                'type' => $question->type,
                'content' => $question->content,
                'marks' => $question->marks,
                'options' => $question->options->map(fn (Option $option): array => [
                    'id' => $option->id,
                    'content' => $option->content,
                ])->values(),
            ];
        })->values();

        return Inertia::render('Student/CBTExam', [
            'student' => $this->formatStudentHeader($student),
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'description' => $exam->description,
                'subject' => $exam->subject?->name ?? '-',
                'teacher' => $exam->teacher?->user?->name ?? '-',
                'duration_minutes' => $exam->duration_minutes,
                'max_violations' => $exam->max_violations,
                'end_time' => $exam->end_time->toIso8601String(),
                'has_essay' => $exam->questions->contains('type', 'Esai'),
            ],
            'attempt' => [
                'id' => $attempt->id,
                'started_at' => $attempt->started_at?->toIso8601String(),
                'finished_at' => $attempt->finished_at?->format('d M Y, H:i'),
                'is_finished' => $attempt->finished_at !== null,
                'is_forced_submit' => $attempt->is_forced_submit,
                'score' => $attempt->score,
                'answers' => $attempt->answers ?? [],
                'violations_count' => $attempt->violations_count ?? 0,
            ],
            'remainingSeconds' => $remainingSeconds,
            'questions' => $questions,
        ]);
    }

    public function saveExamAnswer(SaveExamAnswerRequest $request, Exam $exam): JsonResponse
    {
        $student = $this->resolveStudent();
        $validated = $request->validated();

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        Gate::authorize('update', $attempt);

        $now = Carbon::now();
        $deadline = $this->calculateAttemptDeadline($exam, $attempt);

        if ($attempt->finished_at !== null || $now->greaterThanOrEqualTo($deadline)) {
            if ($attempt->finished_at === null) {
                $this->finalizeExamAttempt($exam, $attempt, true);
            }

            return response()->json([
                'saved' => false,
                'forced_submit' => true,
                'message' => 'Waktu ujian telah berakhir atau ujian sudah dikumpulkan.',
            ], 422);
        }

        $question = Question::where('id', $validated['question_id'])
            ->where('exam_id', $exam->id)
            ->first();

        if (! $question) {
            return response()->json([
                'saved' => false,
                'message' => 'Soal tidak termasuk dalam ujian ini.',
            ], 422);
        }

        $optionId = $validated['option_id'] ?? null;
        if ($optionId !== null) {
            $validOption = Option::where('id', $optionId)
                ->where('question_id', $question->id)
                ->exists();

            if (! $validOption) {
                return response()->json([
                    'saved' => false,
                    'message' => 'Opsi jawaban tidak valid untuk soal ini.',
                ], 422);
            }
        }

        $answers = $attempt->answers ?? [];
        $answers[$question->id] = [
            'option_id' => $optionId,
            'text_answer' => $validated['text_answer'] ?? null,
            'updated_at' => $now->toIso8601String(),
        ];

        $attempt->update(['answers' => $answers]);

        return response()->json([
            'saved' => true,
            'forced_submit' => false,
            'remaining_seconds' => max(0, (int) $now->diffInSeconds($deadline, false)),
        ]);
    }

    public function recordExamViolation(RecordExamViolationRequest $request, Exam $exam): JsonResponse
    {
        $student = $this->resolveStudent();
        $validated = $request->validated();

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        Gate::authorize('update', $attempt);

        $result = DB::transaction(function () use ($attempt, $exam, $validated): array {
            $lockedAttempt = ExamAttempt::where('id', $attempt->id)->lockForUpdate()->firstOrFail();

            if ($lockedAttempt->finished_at !== null) {
                return [
                    'violation_count' => $lockedAttempt->violations()->count(),
                    'max_violations' => $exam->max_violations,
                    'forced_submit' => true,
                ];
            }

            ExamViolation::create([
                'exam_attempt_id' => $lockedAttempt->id,
                'type' => $validated['type'],
                'timestamp' => now(),
            ]);

            $violationsCount = $lockedAttempt->violations()->count();
            $forcedSubmit = false;

            if ($violationsCount >= $exam->max_violations) {
                $this->finalizeExamAttempt($exam, $lockedAttempt, true);
                $forcedSubmit = true;
            }

            return [
                'violation_count' => $violationsCount,
                'max_violations' => $exam->max_violations,
                'forced_submit' => $forcedSubmit,
            ];
        });

        return response()->json($result);
    }

    public function submitExam(Request $request, Exam $exam): RedirectResponse
    {
        $student = $this->resolveStudent();

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        Gate::authorize('update', $attempt);

        $submittedAnswers = $request->input('answers', []);
        $isForced = $request->boolean('is_forced_submit', false);

        DB::transaction(function () use ($attempt, $exam, $submittedAnswers, $isForced): void {
            $lockedAttempt = ExamAttempt::where('id', $attempt->id)->lockForUpdate()->firstOrFail();

            if ($lockedAttempt->finished_at !== null) {
                return;
            }

            if (is_array($submittedAnswers) && ! empty($submittedAnswers)) {
                $examQuestionIds = $exam->questions()->pluck('id')->all();
                $merged = $lockedAttempt->answers ?? [];

                foreach ($submittedAnswers as $questionId => $answerPayload) {
                    if (! in_array($questionId, $examQuestionIds, true) || ! is_array($answerPayload)) {
                        continue;
                    }

                    $merged[$questionId] = [
                        'option_id' => $answerPayload['option_id'] ?? null,
                        'text_answer' => $answerPayload['text_answer'] ?? null,
                        'updated_at' => now()->toIso8601String(),
                    ];
                }

                $lockedAttempt->update(['answers' => $merged]);
            }

            $deadline = $this->calculateAttemptDeadline($exam, $lockedAttempt);
            $forceFlag = $isForced || Carbon::now()->greaterThanOrEqualTo($deadline);

            $this->finalizeExamAttempt($exam, $lockedAttempt, $forceFlag);
        });

        return redirect()->route('siswa.cbt.start', $exam->id)->with('success', 'Ujian CBT telah dikumpulkan dan dinilai.');
    }

    private function calculateAttemptDeadline(Exam $exam, ExamAttempt $attempt): Carbon
    {
        $durationEnd = Carbon::parse($attempt->started_at)->addMinutes($exam->duration_minutes);
        $examEnd = Carbon::parse($exam->end_time);

        return $durationEnd->lt($examEnd) ? $durationEnd : $examEnd;
    }

    private function finalizeExamAttempt(Exam $exam, ExamAttempt $attempt, bool $isForcedSubmit = false): void
    {
        if ($attempt->finished_at !== null) {
            return;
        }

        $exam->load('questions.options');
        $answers = $attempt->answers ?? [];

        $totalMarks = (int) $exam->questions->sum('marks');
        $earnedMarks = 0;

        foreach ($exam->questions as $question) {
            if (! in_array($question->type, ['Pilihan Ganda', 'Benar Salah'], true)) {
                continue;
            }

            $selectedOptionId = $answers[$question->id]['option_id'] ?? null;
            if (! $selectedOptionId) {
                continue;
            }

            $correctOption = $question->options->firstWhere('is_correct', true);
            if ($correctOption && $correctOption->id === $selectedOptionId) {
                $earnedMarks += (int) $question->marks;
            }
        }

        $score = $totalMarks > 0 ? round(($earnedMarks / $totalMarks) * 100, 2) : 0.0;

        $attempt->update([
            'finished_at' => now(),
            'is_forced_submit' => $isForcedSubmit || $attempt->is_forced_submit,
            'score' => $score,
        ]);
    }
}
