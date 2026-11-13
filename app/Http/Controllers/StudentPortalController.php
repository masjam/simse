<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\RecordExamViolationRequest;
use App\Http\Requests\Student\SaveExamAnswerRequest;
use App\Http\Requests\Student\SubmitAssignmentRequest;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamViolation;
use App\Models\Grade;
use App\Models\Lesson;
use App\Models\Option;
use App\Models\Question;
use App\Models\Student;
use App\Models\StudentViolation;
use App\Models\Submission;
use App\Models\TeachingJournal;
use App\Models\TeachingSchedule;
use App\Services\StudentAcademicSummaryService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentPortalController extends Controller
{
    public function __construct(
        private readonly StudentAcademicSummaryService $academicSummary,
    ) {}

    private function resolveStudent(): Student
    {
        $relations = [
            'user',
            'studentClass.tingkat',
            'studentClass.teacher.user',
            'studentClass.academicYear',
        ];

        $student = Student::with($relations)
            ->where('user_id', Auth::id())
            ->first();

        if (! $student && Auth::user()?->hasRole('Super Admin')) {
            $student = Student::with($relations)->first();
        }

        abort_if(! $student, 403, 'Profil siswa belum terhubung dengan akun ini.');

        return $student;
    }

    private function formatStudentHeader(Student $student): array
    {
        return [
            'id' => $student->id,
            'name' => $student->user?->name ?? 'Siswa',
            'nisn' => $student->nisn ?? '-',
            'class_name' => $student->studentClass?->name ?? '-',
            'tingkat' => $student->studentClass?->tingkat?->name ?? ($student->studentClass?->level ?? '-'),
            'wali_kelas' => $student->studentClass?->teacher?->user?->name ?? '-',
        ];
    }

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

    public function materiMingguan(Request $request): Response
    {
        $student = $this->resolveStudent();
        $selectedSubjectId = $request->query('subject_id', '');
        $selectedWeek = $request->query('week', '');

        $baseQuery = TeachingJournal::with(['subject', 'teacher.user'])
            ->where('student_class_id', $student->student_class_id)
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        $allClassJournals = (clone $baseQuery)->get();

        $subjects = $allClassJournals
            ->pluck('subject')
            ->filter()
            ->unique('id')
            ->values()
            ->map(fn ($subject): array => [
                'id' => $subject->id,
                'name' => $subject->name,
            ]);

        $availableWeeks = $allClassJournals
            ->map(function (TeachingJournal $journal): ?array {
                if (! $journal->date) {
                    return null;
                }
                $start = $journal->date->copy()->startOfWeek();
                $end = $journal->date->copy()->endOfWeek();

                return [
                    'key' => $journal->date->format('o-\WW'),
                    'label' => 'Minggu '.$journal->date->isoWeek().' ('.$start->format('d M').' - '.$end->format('d M Y').')',
                ];
            })
            ->filter()
            ->unique('key')
            ->values();

        if ($selectedSubjectId !== '') {
            $baseQuery->where('subject_id', $selectedSubjectId);
        }

        if ($selectedWeek !== '' && preg_match('/^(\d{4})-W(\d{2})$/', $selectedWeek, $matches)) {
            $weekStart = Carbon::now()->setISODate((int) $matches[1], (int) $matches[2])->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();
            $baseQuery->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()]);
        }

        $filteredJournals = $baseQuery->get();

        $weeklyGroups = $filteredJournals
            ->groupBy(fn (TeachingJournal $journal): string => $journal->date?->format('o-\WW') ?? 'unknown')
            ->map(function ($weekItems, string $weekKey): array {
                $sampleDate = $weekItems->first()?->date ?? Carbon::now();
                $start = $sampleDate->copy()->startOfWeek();
                $end = $sampleDate->copy()->endOfWeek();
                $isCurrentWeek = $weekKey === Carbon::now()->format('o-\WW');

                $subjectDigests = $weekItems->groupBy('subject_id')->map(function ($subjectItems): array {
                    $subjectName = $subjectItems->first()?->subject?->name ?? '-';
                    $teacherName = $subjectItems->first()?->teacher?->user?->name ?? '-';

                    return [
                        'subject_id' => $subjectItems->first()->subject_id,
                        'subject_name' => $subjectName,
                        'teacher_name' => $teacherName,
                        'meetings_count' => $subjectItems->count(),
                        'topics' => $subjectItems->pluck('topic')->unique()->values()->all(),
                        'entries' => $subjectItems->sortBy('date')->map(fn (TeachingJournal $item): array => [
                            'id' => $item->id,
                            'date' => $item->date?->locale('id')->translatedFormat('l, d M Y') ?? '-',
                            'meeting_number' => $item->meeting_number,
                            'topic' => $item->topic,
                            'summary' => $item->summary,
                            'activities' => $item->activities,
                            'notes' => $item->notes,
                        ])->values()->all(),
                    ];
                })->values()->all();

                return [
                    'week_key' => $weekKey,
                    'week_label' => 'Minggu Ke-'.$sampleDate->isoWeek().' ('.$start->format('d M').' - '.$end->format('d M Y').')',
                    'is_current_week' => $isCurrentWeek,
                    'total_entries' => $weekItems->count(),
                    'subjects' => $subjectDigests,
                ];
            })
            ->values();

        return Inertia::render('Student/MateriMingguan', [
            'student' => $this->formatStudentHeader($student),
            'subjects' => $subjects,
            'weeks' => $availableWeeks,
            'filters' => [
                'subject_id' => $selectedSubjectId,
                'week' => $selectedWeek,
            ],
            'weeklyGroups' => $weeklyGroups,
        ]);
    }

    public function jadwal(): Response
    {
        $student = $this->resolveStudent();

        $dayNames = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        $schedules = TeachingSchedule::with(['subject', 'teacher.user'])
            ->where('student_class_id', $student->student_class_id)
            ->orderBy('day')
            ->orderBy('time_start')
            ->get()
            ->map(function (TeachingSchedule $schedule) use ($dayNames): array {
                return [
                    'id' => $schedule->id,
                    'day' => $schedule->day,
                    'day_name' => $dayNames[$schedule->day] ?? '-',
                    'subject' => $schedule->subject?->name ?? '-',
                    'subject_code' => $schedule->subject?->code ?? '-',
                    'teacher' => $schedule->teacher?->user?->name ?? '-',
                    'time_start' => substr((string) $schedule->time_start, 0, 5),
                    'time_end' => substr((string) $schedule->time_end, 0, 5),
                    'room' => $schedule->room ?? '-',
                    'jpl' => $schedule->jpl ?? 2,
                ];
            });

        return Inertia::render('Student/Jadwal', [
            'student' => $this->formatStudentHeader($student),
            'schedules' => $schedules,
            'todayIso' => Carbon::now()->dayOfWeekIso,
        ]);
    }

    public function nilai(): Response
    {
        $student = $this->resolveStudent();

        $grades = Grade::with(['subject', 'teacher.user'])
            ->where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->get();

        $raportSubjects = $grades->groupBy('subject_id')->map(function ($subjectGrades): array {
            $first = $subjectGrades->first();
            $tugasValues = $subjectGrades->where('type', 'Tugas')->pluck('score');
            $kuisValues = $subjectGrades->where('type', 'Kuis')->pluck('score');
            $utsScore = $subjectGrades->where('type', 'UTS')->avg('score');
            $uasScore = $subjectGrades->where('type', 'UAS')->avg('score');

            $tugasAvg = $tugasValues->isNotEmpty() ? (float) $tugasValues->avg() : null;
            $kuisAvg = $kuisValues->isNotEmpty() ? (float) $kuisValues->avg() : null;

            $weightedParts = [];
            $weightTotal = 0.0;

            if ($tugasAvg !== null) {
                $weightedParts[] = $tugasAvg * 0.30;
                $weightTotal += 0.30;
            }
            if ($kuisAvg !== null) {
                $weightedParts[] = $kuisAvg * 0.20;
                $weightTotal += 0.20;
            }
            if ($utsScore !== null) {
                $weightedParts[] = (float) $utsScore * 0.25;
                $weightTotal += 0.25;
            }
            if ($uasScore !== null) {
                $weightedParts[] = (float) $uasScore * 0.25;
                $weightTotal += 0.25;
            }

            $finalScore = $weightTotal > 0 ? round(array_sum($weightedParts) / $weightTotal, 1) : 0.0;

            $predikat = 'B';
            $catatan = 'Menunjukkan pemahaman yang baik dan aktif dalam kegiatan pembelajaran.';
            if ($finalScore >= 90) {
                $predikat = 'A';
                $catatan = 'Sangat baik dalam memahami materi pembelajaran, berakhlak mulia, dan konsisten berprestasi.';
            } elseif ($finalScore >= 80) {
                $predikat = 'B+';
                $catatan = 'Baik dalam penguasaan materi, tekun mengerjakan tugas, dan santun kepada guru.';
            } elseif ($finalScore >= 70) {
                $predikat = 'C+';
                $catatan = 'Cukup memahami materi, perlu bimbingan tambahan dalam pemahaman konsep.';
            } elseif ($weightTotal > 0) {
                $predikat = 'D';
                $catatan = 'Perlu pendampingan intensif dalam pembelajaran dan pengulangan materi.';
            }

            return [
                'subject_id' => $first->subject_id,
                'subject_name' => $first->subject?->name ?? '-',
                'teacher_name' => $first->teacher?->user?->name ?? '-',
                'tugas_avg' => $tugasAvg !== null ? round($tugasAvg, 1) : null,
                'kuis_avg' => $kuisAvg !== null ? round($kuisAvg, 1) : null,
                'uts' => $utsScore !== null ? round((float) $utsScore, 1) : null,
                'uas' => $uasScore !== null ? round((float) $uasScore, 1) : null,
                'nilai_akhir' => $finalScore,
                'predikat' => $predikat,
                'catatan' => $catatan,
            ];
        })->values();

        $detailedGrades = $grades->map(fn (Grade $grade): array => [
            'id' => $grade->id,
            'subject' => $grade->subject?->name ?? '-',
            'teacher' => $grade->teacher?->user?->name ?? '-',
            'type' => $grade->type,
            'name' => $grade->name ?? $grade->type,
            'score' => (float) $grade->score,
            'notes' => $grade->notes,
            'date' => $grade->created_at?->format('d M Y') ?? '-',
        ])->values();

        return Inertia::render('Student/Nilai', [
            'student' => $this->formatStudentHeader($student),
            'raportSubjects' => $raportSubjects,
            'grades' => $detailedGrades,
            'achievements' => $this->academicSummary->approvedAchievements($student),
        ]);
    }

    public function presensi(): Response
    {
        $student = $this->resolveStudent();

        $records = Attendance::where('user_id', $student->user_id)
            ->orderByDesc('date')
            ->get();

        $summary = [
            'total' => $records->count(),
            'hadir' => $records->where('status', 'Hadir')->count(),
            'terlambat' => $records->where('status', 'Terlambat')->count(),
            'izin' => $records->where('status', 'Izin')->count(),
            'sakit' => $records->where('status', 'Sakit')->count(),
            'alpa' => $records->where('status', 'Alpa')->count(),
        ];

        $attendances = $records->map(fn (Attendance $attendance): array => [
            'id' => $attendance->id,
            'date' => Carbon::parse($attendance->date)->format('d M Y'),
            'day_name' => Carbon::parse($attendance->date)->locale('id')->translatedFormat('l'),
            'time_in' => $attendance->time_in ? substr((string) $attendance->time_in, 0, 5) : '-',
            'time_out' => $attendance->time_out ? substr((string) $attendance->time_out, 0, 5) : '-',
            'status' => $attendance->status,
            'leave_reason' => $attendance->leave_reason,
        ])->values();

        return Inertia::render('Student/Presensi', [
            'student' => $this->formatStudentHeader($student),
            'summary' => $summary,
            'attendances' => $attendances,
        ]);
    }

    public function lms(Request $request): Response
    {
        $student = $this->resolveStudent();
        $selectedSubjectId = $request->query('subject_id', '');

        $lessonsQuery = Lesson::with([
            'subject',
            'teacher.user',
            'assignments.submissions' => fn ($query) => $query->where('student_id', $student->id),
        ])
            ->where('student_class_id', $student->student_class_id)
            ->orderBy('order');

        $allClassLessons = (clone $lessonsQuery)->get();

        $subjects = $allClassLessons
            ->pluck('subject')
            ->filter()
            ->unique('id')
            ->values()
            ->map(fn ($subject): array => [
                'id' => $subject->id,
                'name' => $subject->name,
            ]);

        if ($selectedSubjectId !== '') {
            $lessonsQuery->where('subject_id', $selectedSubjectId);
        }

        $lessons = $lessonsQuery->get()->map(function (Lesson $lesson): array {
            $hasPrivateFile = $lesson->attachment && Storage::disk('local')->exists($lesson->attachment);

            return [
                'id' => $lesson->id,
                'order' => $lesson->order,
                'title' => $lesson->title,
                'content' => $lesson->content,
                'subject' => $lesson->subject?->name ?? '-',
                'subject_id' => $lesson->subject_id,
                'teacher' => $lesson->teacher?->user?->name ?? '-',
                'attachment' => $lesson->attachment,
                'has_downloadable_file' => $hasPrivateFile,
                'attachment_url' => $lesson->attachment ? route('siswa.lms.attachment', $lesson->id) : null,
                'video_url' => $lesson->video_url,
                'date' => $lesson->created_at?->format('d M Y') ?? '-',
                'assignments' => $lesson->assignments->map(function (Assignment $assignment): array {
                    $submission = $assignment->submissions->first();

                    return [
                        'id' => $assignment->id,
                        'title' => $assignment->title,
                        'description' => $assignment->description,
                        'due_date' => $assignment->due_date->format('d M Y, H:i'),
                        'is_overdue' => $assignment->due_date->isPast(),
                        'max_score' => $assignment->max_score,
                        'submission' => $submission ? [
                            'id' => $submission->id,
                            'text_answer' => $submission->text_answer,
                            'has_file' => (bool) $submission->file_path,
                            'file_url' => $submission->file_path ? route('siswa.submissions.download', $submission->id) : null,
                            'score' => $submission->score,
                            'feedback' => $submission->feedback,
                            'submitted_at' => $submission->submitted_at?->format('d M Y, H:i') ?? '-',
                        ] : null,
                    ];
                })->values(),
            ];
        })->values();

        $submissions = Submission::where('student_id', $student->id)->get();
        $totalSubmissions = $submissions->count();
        $totalXp = (int) $submissions->sum('score');
        $highScoreCount = $submissions->where('score', '>=', 90)->count();

        $level = match (true) {
            $totalXp >= 500 => ['level' => 5, 'title' => 'Cendekiawan Belia Mendunia', 'icon' => '👑'],
            $totalXp >= 300 => ['level' => 4, 'title' => 'Pendekar Cilik Al Mujahidin', 'icon' => '🌟'],
            $totalXp >= 150 => ['level' => 3, 'title' => 'Bintang Kelas', 'icon' => '⭐'],
            $totalXp >= 50 => ['level' => 2, 'title' => 'Santri Rajin', 'icon' => '📚'],
            default => ['level' => 1, 'title' => 'Penuntut Ilmu Pemula', 'icon' => '🌱'],
        };

        $badges = [
            [
                'name' => 'Langkah Pertama',
                'desc' => 'Mengumpulkan tugas perdana',
                'icon' => '🎯',
                'unlocked' => $totalSubmissions >= 1,
            ],
            [
                'name' => 'Kolektor Nilai A',
                'desc' => 'Meraih nilai 90 ke atas',
                'icon' => '💎',
                'unlocked' => $highScoreCount >= 1,
            ],
            [
                'name' => 'Santri Tekun',
                'desc' => 'Menyelesaikan 3 tugas pembelajaran',
                'icon' => '🔥',
                'unlocked' => $totalSubmissions >= 3,
            ],
            [
                'name' => 'Bintang Mujahidin',
                'desc' => 'Mengumpulkan lebih dari 300 Poin XP',
                'icon' => '🏆',
                'unlocked' => $totalXp >= 300,
            ],
        ];

        return Inertia::render('Student/LMS', [
            'student' => $this->formatStudentHeader($student),
            'subjects' => $subjects,
            'selectedSubjectId' => $selectedSubjectId,
            'lessons' => $lessons,
            'gamification' => [
                'total_xp' => $totalXp,
                'total_submissions' => $totalSubmissions,
                'level' => $level,
                'badges' => $badges,
            ],
        ]);
    }

    public function downloadLessonAttachment(Lesson $lesson): StreamedResponse
    {
        Gate::authorize('view', $lesson);

        if (! $lesson->attachment || ! Storage::disk('local')->exists($lesson->attachment)) {
            abort(404, 'Lampiran materi tidak ditemukan.');
        }

        return Storage::disk('local')->download($lesson->attachment);
    }

    public function submitAssignment(SubmitAssignmentRequest $request, Assignment $assignment): RedirectResponse
    {
        Gate::authorize('submit', $assignment);

        $student = $this->resolveStudent();
        $validated = $request->validated();

        $existing = Submission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        $filePath = $existing?->file_path;

        if ($request->hasFile('file')) {
            if ($filePath && Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
            }

            $filePath = $request->file('file')->store('submissions', 'local');
        }

        Submission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
            ],
            [
                'text_answer' => $validated['text_answer'] ?? $existing?->text_answer,
                'file_path' => $filePath,
                'submitted_at' => now(),
            ]
        );

        return redirect()->back()->with('success', 'Tugas berhasil dikumpulkan ke guru.');
    }

    public function downloadSubmissionFile(Submission $submission): StreamedResponse
    {
        Gate::authorize('view', $submission);

        if (! $submission->file_path || ! Storage::disk('local')->exists($submission->file_path)) {
            abort(404, 'File pengumpulan tugas tidak ditemukan.');
        }

        return Storage::disk('local')->download($submission->file_path);
    }

    public function cbt(): Response
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

    public function kts(): Response
    {
        $student = $this->resolveStudent();
        $name = $student->user?->name ?? 'Siswa';

        $photoUrl = $student->photo && Storage::disk('public')->exists($student->photo)
            ? asset('storage/'.$student->photo)
            : 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=0f766e&color=ffffff&size=240';

        $tingkatName = $student->studentClass?->tingkat?->name
            ?? ($student->studentClass?->level ? 'Kelas '.$student->studentClass->level : '-');

        return Inertia::render('Student/KTS', [
            'student' => [
                'id' => $student->id,
                'name' => $name,
                'nisn' => $student->nisn ?? '-',
                'nik' => $student->nik ?? '-',
                'qr_value' => $student->qr_code ?: ($student->nisn ?: $student->id),
                'class_name' => $student->studentClass?->name ?? '-',
                'tingkat' => $tingkatName,
                'academic_year' => $student->studentClass?->academicYear?->name ?? '2026/2027',
                'gender' => $student->gender === 'P' ? 'Perempuan' : ($student->gender === 'L' ? 'Laki-laki' : '-'),
                'photo_url' => $photoUrl,
            ],
        ]);
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
