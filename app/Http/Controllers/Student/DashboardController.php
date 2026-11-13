<?php

namespace App\Http\Controllers\Student;

use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\StudentViolation;
use App\Models\TeachingJournal;
use App\Models\TeachingSchedule;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends BaseStudentController
{
    public function dashboard(): Response
    {
        $student = $this->resolveStudent();
        $now = Carbon::now();
        $todayIso = $now->dayOfWeekIso;

        $dayNames = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        $todaySchedules = TeachingSchedule::with(['subject', 'teacher.user'])
            ->where('student_class_id', $student->student_class_id)
            ->where('day', $todayIso)
            ->orderBy('time_start')
            ->get()
            ->map(function (TeachingSchedule $schedule): array {
                return [
                    'id' => $schedule->id,
                    'subject' => $schedule->subject?->name ?? '-',
                    'teacher' => $schedule->teacher?->user?->name ?? '-',
                    'time_start' => substr((string) $schedule->time_start, 0, 5),
                    'time_end' => substr((string) $schedule->time_end, 0, 5),
                    'room' => $schedule->room ?? '-',
                    'jpl' => $schedule->jpl ?? 2,
                ];
            });

        $upcomingAssignments = Assignment::with([
            'lesson.subject',
            'submissions' => fn ($query) => $query->where('student_id', $student->id),
        ])
            ->whereHas('lesson', fn ($query) => $query->where('student_class_id', $student->student_class_id))
            ->where('due_date', '>=', $now->copy()->subDay())
            ->orderBy('due_date')
            ->take(5)
            ->get()
            ->map(function (Assignment $assignment): array {
                $submission = $assignment->submissions->first();

                return [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'subject' => $assignment->lesson?->subject?->name ?? '-',
                    'due_date' => $assignment->due_date->format('d M Y, H:i'),
                    'is_overdue' => $assignment->due_date->isPast(),
                    'is_submitted' => (bool) $submission,
                    'score' => $submission?->score,
                ];
            });

        $activeExams = Exam::with([
            'subject',
            'questions',
            'attempts' => fn ($query) => $query->where('student_id', $student->id),
        ])
            ->where('is_active', true)
            ->where('end_time', '>=', $now)
            ->orderBy('start_time')
            ->take(5)
            ->get()
            ->map(function (Exam $exam) use ($now): array {
                $attempt = $exam->attempts->first();
                $canStart = $now->between($exam->start_time, $exam->end_time) && ! $attempt?->finished_at;

                return [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'subject' => $exam->subject?->name ?? '-',
                    'duration_minutes' => $exam->duration_minutes,
                    'questions_count' => $exam->questions->count(),
                    'start_time' => $exam->start_time->format('d M Y, H:i'),
                    'end_time' => $exam->end_time->format('d M Y, H:i'),
                    'can_start' => $canStart,
                    'is_finished' => (bool) $attempt?->finished_at,
                    'score' => $attempt?->score,
                ];
            });

        $violations = StudentViolation::with('violationPoint')
            ->where('student_id', $student->id)
            ->get();
        $totalPoints = (int) $violations->sum(fn (StudentViolation $violation): int => (int) ($violation->violationPoint?->points ?? 0));

        $spStatus = 'Aman';
        if ($totalPoints >= 75) {
            $spStatus = 'SP 3';
        } elseif ($totalPoints >= 50) {
            $spStatus = 'SP 2';
        } elseif ($totalPoints >= 25) {
            $spStatus = 'SP 1';
        }

        $attendances = Attendance::where('user_id', $student->user_id)->get();
        $attendanceTotal = $attendances->count();
        $presentCount = $attendances->whereIn('status', ['Hadir', 'Terlambat'])->count();
        $attendanceRate = $attendanceTotal > 0 ? round(($presentCount / $attendanceTotal) * 100) : 100;

        $gradeAvg = Grade::where('student_id', $student->id)->avg('score');

        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();

        $weeklyJournals = TeachingJournal::with(['subject', 'teacher.user'])
            ->where('student_class_id', $student->student_class_id)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();

        $weeklyMaterials = $weeklyJournals->take(4)->map(fn (TeachingJournal $journal): array => [
            'id' => $journal->id,
            'subject' => $journal->subject?->name ?? '-',
            'teacher' => $journal->teacher?->user?->name ?? '-',
            'date' => $journal->date?->locale('id')->translatedFormat('D, d M Y') ?? '-',
            'meeting_number' => $journal->meeting_number,
            'topic' => $journal->topic,
            'summary' => $journal->summary,
        ])->values();

        return Inertia::render('Student/Dashboard', [
            'student' => $this->formatStudentHeader($student),
            'todayName' => $dayNames[$todayIso] ?? 'Hari Ini',
            'todayDate' => $now->format('d M Y'),
            'weekLabel' => $weekStart->format('d M').' - '.$weekEnd->format('d M Y'),
            'todaySchedules' => $todaySchedules,
            'upcomingAssignments' => $upcomingAssignments,
            'activeExams' => $activeExams,
            'weeklyMaterials' => $weeklyMaterials,
            'summary' => [
                'pending_tasks' => $upcomingAssignments->where('is_submitted', false)->count(),
                'active_exams_count' => $activeExams->where('is_finished', false)->count(),
                'weekly_materials_count' => $weeklyJournals->count(),
                'violation_points' => $totalPoints,
                'sp_status' => $spStatus,
                'attendance_rate' => $attendanceRate,
                'average_grade' => $gradeAvg !== null ? round((float) $gradeAvg, 1) : null,
            ],
        ]);
    }
}
