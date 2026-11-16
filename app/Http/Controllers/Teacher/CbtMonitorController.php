<?php

namespace App\Http\Controllers\Teacher;

use App\Models\AuditLog;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Services\Exam\ExamGrader;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CbtMonitorController extends BaseTeacherController
{
    public function show(Exam $exam): Response
    {
        $exam->loadMissing(['subject', 'questions']);

        return Inertia::render('Teacher/CBT/Monitor', [
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'subject' => $exam->subject?->name ?? '-',
                'duration_minutes' => $exam->duration_minutes,
                'max_violations' => $exam->max_violations,
                'total_questions' => $exam->questions->count(),
                'start_time' => $exam->start_time->format('d/m/Y H:i'),
                'end_time' => $exam->end_time->format('d/m/Y H:i'),
                'is_active' => $exam->is_active,
                'is_remedial' => $exam->is_remedial,
            ],
            'monitorData' => $this->buildMonitorPayload($exam),
        ]);
    }

    /**
     * Super-lightweight JSON endpoint for polling every 5 seconds.
     */
    public function poll(Exam $exam): JsonResponse
    {
        return response()->json($this->buildMonitorPayload($exam));
    }

    /**
     * Build unified monitoring data structure (Reverb-compatible).
     */
    protected function buildMonitorPayload(Exam $exam): array
    {
        $exam->loadMissing('questions');
        $totalQuestions = $exam->questions->count();
        $now = Carbon::now();

        // If exam is remedial, only target students; otherwise all enrolled students
        $studentsQuery = Student::with([
            'user',
            'studentClass',
            'attempts' => fn ($q) => $q->where('exam_id', $exam->id)->with('violations'),
        ]);

        if ($exam->is_remedial) {
            $studentsQuery->whereHas('examTargets', fn ($q) => $q->where('exam_id', $exam->id));
        }

        $students = $studentsQuery->get();

        $rows = [];
        $counts = [
            'total' => $students->count(),
            'not_started' => 0,
            'in_progress' => 0,
            'finished' => 0,
            'violations_total' => 0,
        ];

        foreach ($students as $student) {
            $attempt = $student->attempts->first();
            $status = 'Belum Mulai';
            $progress = 0;
            $answeredCount = 0;
            $violationsCount = 0;
            $remainingSeconds = null;
            $lastActive = null;

            if ($attempt) {
                $violationsCount = $attempt->violations->count();
                $counts['violations_total'] += $violationsCount;

                $answers = $attempt->answers ?? [];
                foreach ($answers as $ans) {
                    if (! empty($ans['option_id']) || ! empty($ans['option_ids']) || ! empty($ans['text_answer']) || ! empty($ans['pairs']) || ! empty($ans['blanks'])) {
                        $answeredCount++;
                    }
                }
                $progress = $totalQuestions > 0 ? round(($answeredCount / $totalQuestions) * 100) : 0;

                if ($attempt->finished_at !== null) {
                    $status = $attempt->is_forced_submit ? 'Selesai (Dipaksa)' : 'Selesai';
                    $counts['finished']++;
                } else {
                    $status = 'Mengerjakan';
                    $counts['in_progress']++;

                    // Calculate remaining seconds including extra time
                    $deadline = Carbon::parse($attempt->started_at)
                        ->addMinutes($exam->duration_minutes + ($attempt->extra_time_minutes ?? 0));
                    $examEnd = Carbon::parse($exam->end_time);
                    $effectiveEnd = $deadline->lt($examEnd) ? $deadline : $examEnd;

                    $remainingSeconds = max(0, $now->diffInSeconds($effectiveEnd, false));
                }

                $lastActive = $attempt->updated_at ? $attempt->updated_at->diffForHumans() : '-';
            } else {
                $counts['not_started']++;
            }

            $rows[] = [
                'student_id' => $student->id,
                'nis' => $student->nis,
                'name' => $student->user?->name ?? 'Siswa',
                'class' => $student->studentClass?->name ?? '-',
                'status' => $status,
                'attempt_id' => $attempt?->id,
                'progress_pct' => $progress,
                'answered_count' => $answeredCount,
                'total_questions' => $totalQuestions,
                'violations_count' => $violationsCount,
                'max_violations' => $exam->max_violations,
                'extra_time_minutes' => $attempt?->extra_time_minutes ?? 0,
                'remaining_seconds' => $remainingSeconds,
                'score' => $attempt?->score,
                'last_active' => $lastActive,
            ];
        }

        return [
            'timestamp' => $now->toIso8601String(),
            'counts' => $counts,
            'students' => $rows,
        ];
    }

    public function addExtraTime(Request $request, ExamAttempt $attempt): JsonResponse
    {
        $validated = $request->validate([
            'minutes' => 'required|integer|min:1|max:60',
        ]);

        $attempt->increment('extra_time_minutes', $validated['minutes']);

        // Log audit
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'updated',
            'auditable_type' => ExamAttempt::class,
            'auditable_id' => $attempt->id,
            'old_values' => ['extra_time_minutes' => $attempt->extra_time_minutes - $validated['minutes']],
            'new_values' => ['extra_time_minutes' => $attempt->extra_time_minutes],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Berhasil menambah {$validated['minutes']} menit waktu pengerjaan.",
            'extra_time_minutes' => $attempt->extra_time_minutes,
        ]);
    }

    public function resetViolations(Request $request, ExamAttempt $attempt): JsonResponse
    {
        $oldViolationsCount = $attempt->violations()->count();

        // Delete recorded violations for this attempt and allow continuation
        $attempt->violations()->delete();
        $attempt->update(['is_unlocked' => true]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'updated',
            'auditable_type' => ExamAttempt::class,
            'auditable_id' => $attempt->id,
            'old_values' => ['violations_count' => $oldViolationsCount],
            'new_values' => ['violations_count' => 0, 'is_unlocked' => true],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pelanggaran siswa berhasil di-reset. Siswa dapat melanjutkan ujian.',
        ]);
    }

    public function forceSubmit(Request $request, ExamAttempt $attempt): JsonResponse
    {
        if ($attempt->finished_at !== null) {
            return response()->json(['message' => 'Ujian siswa ini sudah selesai.'], 400);
        }

        $grader = app(ExamGrader::class);
        $grader->finalizeAttempt($attempt, true);

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'updated',
            'auditable_type' => ExamAttempt::class,
            'auditable_id' => $attempt->id,
            'old_values' => ['finished_at' => null],
            'new_values' => ['finished_at' => now(), 'is_forced_submit' => true],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ujian siswa berhasil dipaksa kumpul dan dinilai.',
        ]);
    }
}
