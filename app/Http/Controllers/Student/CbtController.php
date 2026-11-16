<?php

namespace App\Http\Controllers\Student;

use App\Http\Requests\Student\RecordExamViolationRequest;
use App\Http\Requests\Student\SaveExamAnswerRequest;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamViolation;
use App\Models\Option;
use App\Models\Question;
use App\Services\Exam\ExamGrader;
use App\Services\Gamification\PointService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
                $query->where(function ($q) use ($student) {
                    $q->where('is_active', true);
                    // Filter remedial: if remedial, student must be targeted
                    $q->where(function ($sub) use ($student) {
                        $sub->where('is_remedial', false)
                            ->orWhereHas('targets', fn ($t) => $t->where('student_id', $student->id));
                    });
                })->orWhereHas('attempts', fn ($q) => $q->where('student_id', $student->id));
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
                    'scoring_rule' => $exam->scoring_rule,
                    'is_remedial' => $exam->is_remedial,
                    'questions_count' => $exam->questions->count(),
                    'status' => $status,
                    'can_enter' => $canEnter,
                    'attempt' => $attempt ? [
                        'id' => $attempt->id,
                        'started_at' => $attempt->started_at?->format('d M Y, H:i'),
                        'finished_at' => $attempt->finished_at?->format('d M Y, H:i'),
                        'score' => $attempt->score,
                        'is_forced_submit' => $attempt->is_forced_submit,
                        'needs_manual_grading' => $attempt->needs_manual_grading,
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

        // Verify remedial targeting
        if ($exam->is_remedial && ! $exam->targets()->where('student_id', $student->id)->exists()) {
            return redirect()->route('siswa.cbt')->with('error', 'Anda tidak terdaftar dalam paket ujian remedial ini.');
        }

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

            // Generate deterministic seed and question order
            $seed = crc32($student->id.'_'.$exam->id);
            $questionIds = $exam->questions()->pluck('id')->all();

            if ($exam->randomize_questions) {
                mt_srand($seed);
                shuffle($questionIds);
            }

            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'seed' => $seed,
                'question_order' => $questionIds,
                'answers' => [],
                'started_at' => $now,
            ]);
        } else {
            Gate::authorize('update', $attempt);
        }

        if ($attempt->finished_at === null) {
            $deadline = $this->calculateAttemptDeadline($exam, $attempt);
            if ($now->greaterThanOrEqualTo($deadline)) {
                app(ExamGrader::class)->finalizeAttempt($attempt, true);
                $attempt->refresh();
            }
        }

        $exam->load(['subject', 'teacher.user', 'questions.options']);
        $attempt->loadCount('violations');

        $remainingSeconds = $attempt->finished_at
            ? 0
            : max(0, (int) $now->diffInSeconds($this->calculateAttemptDeadline($exam, $attempt), false));

        // Order questions according to attempt's question_order
        $orderedQuestionIds = $attempt->question_order ?: $exam->questions->pluck('id')->all();
        $questionsMap = $exam->questions->keyBy('id');

        $questions = [];
        $seed = $attempt->seed ?: crc32($student->id.'_'.$exam->id);

        foreach ($orderedQuestionIds as $qId) {
            $question = $questionsMap->get($qId);
            if (! $question) {
                continue;
            }

            // Prepare options (sanitize answers and shuffle if configured)
            $optionsList = $question->options->map(fn (Option $option): array => [
                'id' => $option->id,
                'content' => $option->content,
                'image_url' => $option->image_path ? route('cbt.image', ['path' => basename($option->image_path)]) : null,
            ])->all();

            if ($exam->randomize_options && in_array($question->type, ['Pilihan Ganda', 'Ganda Kompleks'], true)) {
                mt_srand($seed + crc32($question->id));
                shuffle($optionsList);
            }

            // For Menjodohkan: extract distinct available target pairs for dropdowns
            $pairChoices = [];
            if ($question->type === 'Menjodohkan') {
                $rawPairs = $question->options->pluck('pair_match')->filter()->unique()->values()->all();
                mt_srand($seed + 999);
                shuffle($rawPairs);
                $pairChoices = $rawPairs;
            }

            $questions[] = [
                'id' => $question->id,
                'type' => $question->type,
                'content' => $question->content,
                'marks' => $question->marks,
                'image_url' => $question->image_path ? route('cbt.image', ['path' => basename($question->image_path)]) : null,
                'metadata' => [
                    'blanks_count' => count($question->metadata['blanks'] ?? ($question->type === 'Isian Rumpang' ? [0] : [])),
                ],
                'options' => $optionsList,
                'pair_choices' => $pairChoices,
            ];
        }

        // Student's class level (for mode anak detection: level 1-3)
        $studentLevel = (int) ($student->studentClass?->level ?? 4);

        return Inertia::render('Student/CBTExam', [
            'student' => $this->formatStudentHeader($student),
            'studentLevel' => $studentLevel,
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
            'serverNow' => $now->toIso8601String(),
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
                app(ExamGrader::class)->finalizeAttempt($attempt, true);
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

        $answers = $attempt->answers ?? [];

        // Store flexible answer payload supporting all question types
        $answers[$question->id] = [
            'option_id' => $validated['option_id'] ?? null,
            'option_ids' => $validated['option_ids'] ?? null,
            'pairs' => $validated['pairs'] ?? null,
            'blanks' => $validated['blanks'] ?? null,
            'text_answer' => $validated['text_answer'] ?? null,
            'updated_at' => $now->toIso8601String(),
        ];

        $attempt->update(['answers' => $answers]);

        return response()->json([
            'saved' => true,
            'forced_submit' => false,
            'server_now' => $now->toIso8601String(),
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
                app(ExamGrader::class)->finalizeAttempt($lockedAttempt, true);
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

        DB::transaction(function () use ($attempt, $exam, $submittedAnswers, $isForced, $student): void {
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
                        'option_ids' => $answerPayload['option_ids'] ?? null,
                        'pairs' => $answerPayload['pairs'] ?? null,
                        'blanks' => $answerPayload['blanks'] ?? null,
                        'text_answer' => $answerPayload['text_answer'] ?? null,
                        'updated_at' => now()->toIso8601String(),
                    ];
                }

                $lockedAttempt->update(['answers' => $merged]);
            }

            $deadline = $this->calculateAttemptDeadline($exam, $lockedAttempt);
            $forceFlag = $isForced || Carbon::now()->greaterThanOrEqualTo($deadline);

            app(ExamGrader::class)->finalizeAttempt($lockedAttempt, $forceFlag);
            $lockedAttempt->refresh();

            // Sprint 5B: Gamifikasi CBT
            $pointService = app(PointService::class);
            $pointService->award($student, 'cbt_completed', $lockedAttempt, "Menyelesaikan ujian CBT: {$exam->title}");
            if ($lockedAttempt->score !== null && (float) $lockedAttempt->score >= 90) {
                $pointService->award($student, 'cbt_high_score', $lockedAttempt, "Nilai istimewa CBT (skor {$lockedAttempt->score}): {$exam->title}");
            }
        });

        return redirect()->route('siswa.cbt.start', $exam->id)->with('success', 'Ujian CBT telah dikumpulkan dan dinilai.');
    }

    public function serveImage(string $path): BinaryFileResponse
    {
        $safePath = 'cbt_images/'.basename($path);
        if (! Storage::disk('local')->exists($safePath)) {
            abort(404, 'Gambar tidak ditemukan.');
        }

        return response()->file(Storage::disk('local')->path($safePath));
    }

    private function calculateAttemptDeadline(Exam $exam, ExamAttempt $attempt): Carbon
    {
        $extra = $attempt->extra_time_minutes ?? 0;
        $durationEnd = Carbon::parse($attempt->started_at)->addMinutes($exam->duration_minutes + $extra);
        $examEnd = Carbon::parse($exam->end_time);

        return $durationEnd->lt($examEnd) ? $durationEnd : $examEnd;
    }
}
