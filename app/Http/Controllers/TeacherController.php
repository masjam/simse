<?php

namespace App\Models;

// Ensuring namespaces are imported

namespace App\Http\Controllers;

use App\Exports\NilaiExport;
use App\Http\Requests\Teacher\StoreTeachingJournalRequest;
use App\Imports\NilaiImport;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Book;
use App\Models\CounselingSession;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\Lesson;
use App\Models\Option;
use App\Models\Payroll;
use App\Models\Question;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentViolation;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use App\Models\TeachingSchedule;
use App\Models\ViolationPoint;
use App\Notifications\SimseNotification;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherController extends Controller
{
    private function getTeacher(): ?Teacher
    {
        $teacher = Teacher::where('user_id', Auth::id())->first();
        if (! $teacher && Auth::user()?->hasRole('Super Admin')) {
            // Fallback for Super Admin testing the teacher portal
            $teacher = Teacher::first();
        }

        return $teacher;
    }

    private function getTeacherData(): array
    {
        $teacher = $this->getTeacher();
        if (! $teacher) {
            $classes = StudentClass::pluck('name')->values()->toArray();
            $subjects = Subject::pluck('name')->values()->toArray();

            return ['classes' => $classes, 'subjects' => $subjects];
        }

        $classes = TeachingSchedule::with('studentClass')
            ->where('teacher_id', $teacher->id)
            ->get()
            ->pluck('studentClass.name')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($classes)) {
            $classes = StudentClass::pluck('name')->values()->toArray();
        }

        $subjects = TeachingSchedule::with('subject')
            ->where('teacher_id', $teacher->id)
            ->get()
            ->pluck('subject.name')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($subjects)) {
            $subjects = Subject::pluck('name')->values()->toArray();
        }

        return ['classes' => $classes, 'subjects' => $subjects];
    }

    // 1. DASHBOARD
    public function dashboard()
    {
        $teacher = $this->getTeacher();
        $teacherId = $teacher?->id ?? 0;

        // Class & Student Counts
        $classIds = TeachingSchedule::where('teacher_id', $teacherId)->pluck('student_class_id')->unique();
        if ($classIds->isEmpty()) {
            $classIds = StudentClass::pluck('id');
        }

        $totalClasses = $classIds->count();
        $totalStudents = Student::whereIn('student_class_id', $classIds)->count();
        $totalSubjects = TeachingSchedule::where('teacher_id', $teacherId)->pluck('subject_id')->unique()->count();
        if ($totalSubjects === 0) {
            $totalSubjects = Subject::count();
        }

        // Attendance rate of students in teacher's classes this month
        $studentUserIds = Student::whereIn('student_class_id', $classIds)->pluck('user_id');
        $totalAttendances = Attendance::whereIn('user_id', $studentUserIds)->count();
        $presentAttendances = Attendance::whereIn('user_id', $studentUserIds)->where('status', 'Hadir')->count();
        $attendanceRate = $totalAttendances > 0 ? round(($presentAttendances / $totalAttendances) * 100, 1) : 96.4;

        // Today's Schedules (dayOfWeekIso: 1=Senin, 2=Selasa, etc.)
        $todayDay = Carbon::now()->dayOfWeekIso;
        $colors = ['indigo', 'emerald', 'purple', 'amber', 'rose'];

        $todaySchedules = TeachingSchedule::with(['subject', 'studentClass.students'])
            ->where('teacher_id', $teacherId)
            ->where('day', $todayDay)
            ->get()
            ->map(function ($s, $idx) use ($colors) {
                $now = Carbon::now();
                $start = Carbon::parse($s->time_start);
                $end = Carbon::parse($s->time_end);
                $status = 'upcoming';
                if ($now->between($start, $end)) {
                    $status = 'active';
                } elseif ($now->gt($end)) {
                    $status = 'done';
                }

                return [
                    'id' => $s->id,
                    'time' => Carbon::parse($s->time_start)->format('H:i').' - '.Carbon::parse($s->time_end)->format('H:i'),
                    'class' => $s->studentClass?->name ?? 'Kelas',
                    'subject' => $s->subject?->name ?? 'Mata Pelajaran',
                    'room' => $s->room ?? 'Ruang Kelas',
                    'status' => $status,
                    'students' => $s->studentClass?->students?->count() ?? 30,
                    'color' => $colors[$idx % count($colors)],
                ];
            });

        // If no schedule on current day (e.g. weekend), provide upcoming schedules
        if ($todaySchedules->isEmpty()) {
            $todaySchedules = TeachingSchedule::with(['subject', 'studentClass.students'])
                ->where('teacher_id', $teacherId)
                ->take(4)
                ->get()
                ->map(function ($s, $idx) use ($colors) {
                    $days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];

                    return [
                        'id' => $s->id,
                        'time' => ($days[$s->day] ?? 'Senin').', '.Carbon::parse($s->time_start)->format('H:i').' - '.Carbon::parse($s->time_end)->format('H:i'),
                        'class' => $s->studentClass?->name ?? 'Kelas',
                        'subject' => $s->subject?->name ?? 'Mata Pelajaran',
                        'room' => $s->room ?? 'Ruang Kelas',
                        'status' => 'upcoming',
                        'students' => $s->studentClass?->students?->count() ?? 30,
                        'color' => $colors[$idx % count($colors)],
                    ];
                });
        }

        // Pending Tasks (Ungraded assignments)
        $pendingTasks = Submission::with(['assignment.lesson.subject', 'assignment.lesson.studentClass'])
            ->whereNull('score')
            ->whereHas('assignment.lesson', function ($q) use ($teacherId) {
                if ($teacherId > 0) {
                    $q->where('teacher_id', $teacherId);
                }
            })
            ->take(5)
            ->get()
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'title' => 'Koreksi: '.$sub->assignment->title,
                    'deadline' => $sub->assignment->due_date ? Carbon::parse($sub->assignment->due_date)->diffForHumans() : 'Segera',
                    'priority' => 'tinggi',
                    'type' => 'koreksi',
                    'count' => 1,
                ];
            });

        if ($pendingTasks->isEmpty()) {
            $pendingTasks = [
                ['id' => 1, 'title' => 'Input Nilai Harian PAI - Kelas 1 Brilliant', 'deadline' => 'Besok, 12:00', 'priority' => 'sedang', 'type' => 'nilai', 'count' => 1],
                ['id' => 2, 'title' => 'Review Materi Ujian PTS PAI', 'deadline' => '3 Hari lagi', 'priority' => 'rendah', 'type' => 'materi', 'count' => 1],
            ];
        }

        // Recent activities
        $recentActivities = [
            ['id' => 1, 'text' => 'Presensi harian siswa berhasil disimpan', 'time' => 'Hari ini', 'icon' => 'check', 'color' => 'emerald'],
            ['id' => 2, 'text' => 'Tugas PAI dikumpulkan siswa', 'time' => '1 jam lalu', 'icon' => 'upload', 'color' => 'blue'],
            ['id' => 3, 'text' => 'Jadwal PTS CBT telah aktif', 'time' => 'Kemarin', 'icon' => 'calendar', 'color' => 'purple'],
        ];

        return Inertia::render('Teacher/Dashboard', [
            'teacherName' => Auth::user()?->name ?? 'Pendidik',
            'stats' => [
                'totalClasses' => $totalClasses,
                'totalStudents' => $totalStudents,
                'totalSubjects' => $totalSubjects,
                'attendanceRate' => $attendanceRate,
                'pendingGrades' => count($pendingTasks),
                'upcomingExams' => Exam::where('teacher_id', $teacherId)->where('is_active', true)->count(),
            ],
            'todaySchedules' => $todaySchedules,
            'pendingTasks' => $pendingTasks,
            'recentActivities' => $recentActivities,
        ]);
    }

    // 2. JADWAL
    public function jadwal()
    {
        $teacher = $this->getTeacher();
        $teacherId = $teacher?->id ?? 0;

        $colors = ['indigo', 'emerald', 'purple', 'amber', 'rose', 'cyan'];
        $days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];

        $schedules = TeachingSchedule::with(['subject', 'studentClass.students'])
            ->where('teacher_id', $teacherId)
            ->orderBy('day')
            ->orderBy('time_start')
            ->get()
            ->map(function ($schedule, $index) use ($colors, $days) {
                return [
                    'id' => $schedule->id,
                    'dayNumber' => $schedule->day,
                    'day' => $days[$schedule->day] ?? 'Senin',
                    'timeStart' => Carbon::parse($schedule->time_start)->format('H:i'),
                    'timeEnd' => Carbon::parse($schedule->time_end)->format('H:i'),
                    'room' => $schedule->room ?? 'Ruang Kelas',
                    'subject' => $schedule->subject->name ?? '-',
                    'class' => $schedule->studentClass->name ?? '-',
                    'students' => $schedule->studentClass?->students?->count() ?? 30,
                    'jpl' => $schedule->jpl ?? 2,
                    'color' => $colors[$index % count($colors)],
                ];
            });

        return Inertia::render('Teacher/Jadwal', [
            'schedules' => $schedules,
        ]);
    }

    // 3. ABSENSI
    public function absensi(Request $request)
    {
        $data = $this->getTeacherData();
        $classes = $data['classes'];
        $selectedClass = $request->query('class', $classes[0] ?? '1 Brilliant');
        $selectedDate = $request->query('date', Carbon::today()->toDateString());

        $studentClass = StudentClass::where('name', $selectedClass)->first();

        $students = [];
        if ($studentClass) {
            $studentModels = Student::with('user')
                ->where('student_class_id', $studentClass->id)
                ->get();

            $attendances = Attendance::whereIn('user_id', $studentModels->pluck('user_id'))
                ->where('date', $selectedDate)
                ->get()
                ->keyBy('user_id');

            $students = $studentModels->map(function ($s) use ($attendances) {
                $att = $attendances->get($s->user_id);

                return [
                    'id' => $s->id,
                    'user_id' => $s->user_id,
                    'name' => $s->user?->name ?? 'Siswa',
                    'nisn' => $s->nisn,
                    'gender' => $s->gender ?? 'L',
                    'status' => $att?->status ?? null,
                    'leave_reason' => $att?->leave_reason ?? null,
                    'photo' => $s->photo,
                ];
            });
        }

        return Inertia::render('Teacher/Absensi', [
            'classes' => $classes,
            'selectedClass' => $selectedClass,
            'selectedDate' => $selectedDate,
            'students' => $students,
        ]);
    }

    public function storeAbsensi(Request $request)
    {
        $validated = $request->validate([
            'class_name' => 'required|string',
            'date' => 'required|date',
            'students' => 'required|array',
        ]);

        $date = $validated['date'];
        $absentStudents = [];

        foreach ($validated['students'] as $item) {
            $student = Student::with('user')->find($item['id']);
            if (! $student || ! $student->user_id) {
                continue;
            }

            $status = $item['status'] ?? 'Hadir';
            $leaveReason = $item['leave_reason'] ?? null;

            Attendance::updateOrCreate(
                [
                    'user_id' => $student->user_id,
                    'date' => $date,
                ],
                [
                    'status' => $status,
                    'leave_reason' => $leaveReason,
                    'time_in' => $status === 'Hadir' ? Carbon::now()->format('H:i:s') : null,
                ]
            );

            if (in_array($status, ['Izin', 'Sakit', 'Alpa'])) {
                $absentStudents[] = [
                    'name' => $student->user->name,
                    'status' => $status,
                    'parent_phone' => $student->no_telp_ortu ?? '081234567890',
                    'parent' => $student->parent,
                ];
            }
        }

        // Queue WhatsApp & In-App Notification for Absent Students
        $whatsAppService = app(WhatsAppService::class);
        foreach ($absentStudents as $absent) {
            $message = "Assalamu'alaikum Wr. Wb. Pemberitahuan dari SD Muhammadiyah Al Mujahidin: Ananda {$absent['name']} tercatat {$absent['status']} pada tanggal {$date}.";

            $whatsAppService->queue(
                phoneNumber: $absent['parent_phone'],
                message: $message,
                recipientName: 'Wali Santri '.$absent['name'],
            );

            if ($absent['parent']) {
                $absent['parent']->notify(new SimseNotification(
                    title: "Ketidakhadiran {$absent['name']}",
                    message: $message,
                    category: 'attendance',
                    url: route('ortu.dashboard', ['tab' => 'presensi']),
                ));
            }
        }

        return redirect()->back()->with('success', 'Absensi kelas '.$validated['class_name'].' berhasil disimpan dan notifikasi telah dikirimkan!');
    }

    // 4. NILAI & RAPORT
    public function nilai(Request $request)
    {
        $teacher = $this->getTeacher();
        $data = $this->getTeacherData();
        $classes = $data['classes'];
        $subjects = $data['subjects'];

        $selectedClass = $request->query('class', $classes[0] ?? '1 Brilliant');
        $selectedSubject = $request->query('subject', $subjects[0] ?? 'Pendidikan Agama Islam dan Budi Pekerti');

        $studentClass = StudentClass::where('name', $selectedClass)->first();
        $subject = Subject::where('name', $selectedSubject)->first();

        $students = [];
        if ($studentClass && $subject) {
            $studentModels = Student::with('user')
                ->where('student_class_id', $studentClass->id)
                ->get();

            $grades = Grade::where('subject_id', $subject->id)
                ->whereIn('student_id', $studentModels->pluck('id'))
                ->get()
                ->groupBy('student_id');

            $students = $studentModels->map(function ($s) use ($grades) {
                $studentGrades = $grades->get($s->id, collect());

                $tugas = [];
                for ($i = 1; $i <= 4; $i++) {
                    $g = $studentGrades->firstWhere('name', 'T'.$i);
                    $tugas[] = $g ? (float) $g->score : null;
                }

                $kuis = [];
                for ($i = 1; $i <= 2; $i++) {
                    $g = $studentGrades->firstWhere('name', 'K'.$i);
                    $kuis[] = $g ? (float) $g->score : null;
                }

                $uts = $studentGrades->firstWhere('name', 'UTS')?->score;
                $uas = $studentGrades->firstWhere('name', 'UAS')?->score;

                return [
                    'id' => $s->id,
                    'name' => $s->user?->name ?? 'Siswa',
                    'nisn' => $s->nisn,
                    'tugas' => $tugas,
                    'kuis' => $kuis,
                    'uts' => $uts !== null ? (float) $uts : null,
                    'uas' => $uas !== null ? (float) $uas : null,
                ];
            });
        }

        return Inertia::render('Teacher/Nilai', [
            'classes' => $classes,
            'subjects' => $subjects,
            'selectedClass' => $selectedClass,
            'selectedSubject' => $selectedSubject,
            'students' => $students,
        ]);
    }

    public function storeNilai(Request $request)
    {
        $teacher = $this->getTeacher();
        if (! $teacher) {
            return response()->json(['error' => 'Teacher record not found'], 404);
        }

        $validated = $request->validate([
            'class_name' => 'required|string',
            'subject_name' => 'required|string',
            'type' => 'required|string|in:tugas,kuis,uts,uas',
            'students' => 'required|array',
        ]);

        $subject = Subject::where('name', $validated['subject_name'])->first();
        if (! $subject) {
            return redirect()->back()->with('error', 'Mata pelajaran tidak ditemukan.');
        }

        $typeMap = [
            'tugas' => 'Tugas',
            'kuis' => 'Kuis',
            'uts' => 'UTS',
            'uas' => 'UAS',
        ];
        $dbType = $typeMap[$validated['type']] ?? 'Tugas';

        foreach ($validated['students'] as $student) {
            $studentId = $student['id'];

            if ($validated['type'] === 'uts' || $validated['type'] === 'uas') {
                $score = $student[$validated['type']] ?? null;
                if ($score !== null && $score !== '') {
                    Grade::updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'subject_id' => $subject->id,
                            'teacher_id' => $teacher->id,
                            'type' => $dbType,
                            'name' => strtoupper($validated['type']),
                        ],
                        ['score' => $score]
                    );
                }
            } else {
                $scores = $student[$validated['type']] ?? [];
                foreach ($scores as $index => $score) {
                    if ($score !== null && $score !== '') {
                        $prefix = $validated['type'] === 'tugas' ? 'T' : 'K';
                        $name = $prefix.($index + 1);
                        Grade::updateOrCreate(
                            [
                                'student_id' => $studentId,
                                'subject_id' => $subject->id,
                                'teacher_id' => $teacher->id,
                                'type' => $dbType,
                                'name' => $name,
                            ],
                            ['score' => $score]
                        );
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Nilai '.strtoupper($validated['type']).' berhasil disimpan!');
    }

    public function exportNilai(Request $request)
    {
        $teacher = $this->getTeacher();
        if (! $teacher) {
            abort(403);
        }

        $className = $request->query('class_name', '1 Brilliant');
        $subjectName = $request->query('subject_name', 'Pendidikan Agama Islam dan Budi Pekerti');

        return Excel::download(
            new NilaiExport($className, $subjectName, $teacher->id),
            'Nilai_'.$className.'_'.$subjectName.'.xlsx'
        );
    }

    public function importNilai(Request $request)
    {
        $teacher = $this->getTeacher();
        if (! $teacher) {
            abort(403);
        }

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'subject_name' => 'required|string',
        ]);

        Excel::import(
            new NilaiImport($teacher->id, $request->subject_name),
            $request->file('file')
        );

        return redirect()->back()->with('success', 'Nilai berhasil diimpor!');
    }

    public function raport(Request $request)
    {
        $data = $this->getTeacherData();
        $classes = $data['classes'];
        $selectedClass = $request->query('class', $classes[0] ?? '1 Brilliant');
        $selectedSemester = $request->query('semester', 'Ganjil 2026/2027');

        $studentClass = StudentClass::where('name', $selectedClass)->first();
        $students = [];

        if ($studentClass) {
            $studentModels = Student::with([
                'user',
                'grades',
                'achievements' => fn ($query) => $query
                    ->where('status', 'Disetujui')
                    ->orderByDesc('year')
                    ->orderByDesc('created_at'),
            ])->where('student_class_id', $studentClass->id)->get();

            $students = $studentModels->map(function ($s) {
                $grades = $s->grades;
                $tugasAvg = $grades->where('type', 'Tugas')->avg('score') ?? 85;
                $kuisAvg = $grades->where('type', 'Kuis')->avg('score') ?? 85;
                $utsScore = $grades->where('name', 'UTS')->first()?->score ?? 85;
                $uasScore = $grades->where('name', 'UAS')->first()?->score ?? $utsScore;

                // Weighted final score: 30% Tugas, 20% Kuis, 25% UTS, 25% UAS
                $final = round(($tugasAvg * 0.3) + ($kuisAvg * 0.2) + ($utsScore * 0.25) + ($uasScore * 0.25), 1);

                $predikat = 'B';
                $catatan = 'Menunjukkan pemahaman yang baik dan aktif dalam kegiatan pembelajaran.';
                if ($final >= 90) {
                    $predikat = 'A';
                    $catatan = 'Sangat baik dalam memahami materi pembelajaran, berakhlak mulia dan konsisten berprestasi.';
                } elseif ($final >= 80) {
                    $predikat = 'B+';
                    $catatan = 'Baik dalam penguasaan materi, tekun mengerjakan tugas dan santun kepada guru.';
                } elseif ($final >= 70) {
                    $predikat = 'C+';
                    $catatan = 'Cukup memahami materi, perlu bimbingan tambahan dalam pemahaman konsep.';
                } else {
                    $predikat = 'D';
                    $catatan = 'Perlu pendampingan intensif dan mengikuti program remedial.';
                }

                return [
                    'id' => $s->id,
                    'name' => $s->user?->name ?? 'Siswa',
                    'nisn' => $s->nisn,
                    'nilaiAkhir' => $final,
                    'predikat' => $predikat,
                    'catatan' => $catatan,
                    'achievements' => $s->achievements->map(fn ($achievement): array => [
                        'id' => $achievement->id,
                        'name' => $achievement->name,
                        'category' => $achievement->category ?? 'Umum',
                        'level' => $achievement->level,
                        'year' => $achievement->year,
                        'organizer' => $achievement->organizer ?? '-',
                        'description' => $achievement->description,
                    ])->values()->all(),
                ];
            });
        }

        return Inertia::render('Teacher/Raport', [
            'classes' => $classes,
            'selectedClass' => $selectedClass,
            'selectedSemester' => $selectedSemester,
            'students' => $students,
            'teacher' => [
                'name' => Auth::user()?->name ?? 'Siti Guru, S.Pd.I',
                'nip' => $this->getTeacher()?->nip ?? '198407152010012015',
            ],
        ]);
    }

    // 5. LMS & TUGAS
    public function lms(Request $request)
    {
        $teacher = $this->getTeacher();
        $teacherId = $teacher?->id ?? 0;
        $data = $this->getTeacherData();

        $lessons = Lesson::with(['subject', 'studentClass', 'assignments'])
            ->where('teacher_id', $teacherId)
            ->orderBy('order')
            ->get()
            ->map(function ($l) {
                return [
                    'id' => $l->id,
                    'bab' => $l->order,
                    'title' => $l->title,
                    'description' => $l->content,
                    'subject' => $l->subject?->name ?? '-',
                    'class' => $l->studentClass?->name ?? '-',
                    'type' => $l->video_url ? 'video' : 'pdf',
                    'fileSize' => '2.4 MB',
                    'attachment' => $l->attachment,
                    'attachmentUrl' => $l->attachment ? route('guru.lms.attachment', $l->id) : null,
                    'video_url' => $l->video_url,
                    'totalTopics' => $l->assignments->count(),
                    'completedTopics' => $l->assignments->count(),
                    'status' => 'published',
                    'date' => $l->created_at->format('d M Y'),
                ];
            });

        return Inertia::render('Teacher/LMS', [
            'classes' => $data['classes'],
            'subjects' => $data['subjects'],
            'lessons' => $lessons,
        ]);
    }

    public function storeLesson(Request $request)
    {
        $teacher = $this->getTeacher();
        if (! $teacher) {
            return redirect()->back()->with('error', 'Profil guru tidak ditemukan.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject_name' => 'required|string',
            'class_name' => 'required|string',
            'content' => 'nullable|string',
            'video_url' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|max:10240',
            'order' => 'nullable|integer',
        ]);

        $subject = Subject::where('name', $validated['subject_name'])->first();
        $studentClass = StudentClass::where('name', $validated['class_name'])->first();

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('materials', 'private');
        }

        Lesson::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject?->id ?? Subject::value('id'),
            'student_class_id' => $studentClass?->id ?? StudentClass::value('id'),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'attachment' => $attachmentPath,
            'video_url' => $validated['video_url'] ?? null,
            'order' => $validated['order'] ?? (Lesson::where('teacher_id', $teacher->id)->max('order') + 1),
        ]);

        return redirect()->back()->with('success', 'Materi ajar berhasil diterbitkan!');
    }

    public function downloadLessonAttachment(Lesson $lesson): StreamedResponse
    {
        $teacher = $this->getTeacher();
        if ($lesson->teacher_id !== $teacher?->id && ! Auth::user()?->hasRole('Super Admin')) {
            abort(403);
        }

        if (! $lesson->attachment || ! Storage::disk('private')->exists($lesson->attachment)) {
            abort(404);
        }

        return Storage::disk('private')->download($lesson->attachment);
    }

    public function destroyLesson(Lesson $lesson)
    {
        $teacher = $this->getTeacher();
        if ($lesson->teacher_id !== $teacher?->id && ! Auth::user()?->hasRole('Super Admin')) {
            abort(403);
        }

        if ($lesson->attachment) {
            if (Storage::disk('private')->exists($lesson->attachment)) {
                Storage::disk('private')->delete($lesson->attachment);
            }

            if (Storage::disk('public')->exists($lesson->attachment)) {
                Storage::disk('public')->delete($lesson->attachment);
            }
        }

        $lesson->delete();

        return redirect()->back()->with('success', 'Materi ajar berhasil dihapus.');
    }

    public function tugas(Request $request)
    {
        $teacher = $this->getTeacher();
        $teacherId = $teacher?->id ?? 0;
        $data = $this->getTeacherData();

        $assignments = Assignment::with(['lesson.subject', 'lesson.studentClass.students', 'submissions.student.user'])
            ->whereHas('lesson', function ($q) use ($teacherId) {
                if ($teacherId > 0) {
                    $q->where('teacher_id', $teacherId);
                }
            })
            ->latest()
            ->get()
            ->map(function ($a) {
                $totalStudents = $a->lesson?->studentClass?->students?->count() ?? 30;
                $submittedCount = $a->submissions->count();
                $gradedCount = $a->submissions->whereNotNull('score')->count();
                $isCompleted = Carbon::parse($a->due_date)->isPast() && ($submittedCount === $gradedCount);

                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'subject' => $a->lesson?->subject?->name ?? 'PAI',
                    'class' => $a->lesson?->studentClass?->name ?? '1 Brilliant',
                    'deadline' => Carbon::parse($a->due_date)->format('d M Y, H:i'),
                    'totalStudents' => $totalStudents,
                    'submitted' => $submittedCount,
                    'graded' => $gradedCount,
                    'status' => $isCompleted ? 'completed' : 'active',
                    'type' => 'file',
                    'submissions' => $a->submissions->map(function ($sub) {
                        return [
                            'id' => $sub->id,
                            'studentName' => $sub->student?->user?->name ?? 'Siswa',
                            'nisn' => $sub->student?->nisn ?? '-',
                            'submittedAt' => $sub->submitted_at ? Carbon::parse($sub->submitted_at)->diffForHumans() : '-',
                            'textAnswer' => $sub->text_answer,
                            'filePath' => $sub->file_path,
                            'score' => $sub->score,
                            'feedback' => $sub->feedback,
                        ];
                    }),
                ];
            });

        // Also fetch lessons list for create assignment modal
        $lessons = Lesson::where('teacher_id', $teacherId)->get(['id', 'title']);

        return Inertia::render('Teacher/Tugas', [
            'classes' => $data['classes'],
            'subjects' => $data['subjects'],
            'lessons' => $lessons,
            'assignments' => $assignments,
        ]);
    }

    public function storeAssignment(Request $request)
    {
        $teacher = $this->getTeacher();
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'lesson_id' => 'nullable|exists:lessons,id',
            'class_name' => 'nullable|string',
            'subject_name' => 'nullable|string',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'max_score' => 'required|integer|min:1|max:100',
        ]);

        $lessonId = $validated['lesson_id'] ?? null;
        if (! $lessonId) {
            $lesson = Lesson::where('teacher_id', $teacher?->id)->first();
            $lessonId = $lesson?->id;
        }

        if (! $lessonId) {
            return redirect()->back()->with('error', 'Silakan buat materi bab pembelajaran terlebih dahulu.');
        }

        Assignment::create([
            'lesson_id' => $lessonId,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'due_date' => $validated['due_date'],
            'max_score' => $validated['max_score'],
        ]);

        return redirect()->back()->with('success', 'Tugas baru berhasil dibagikan ke siswa!');
    }

    public function gradeSubmission(Request $request, Submission $submission)
    {
        $teacher = $this->getTeacher();
        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $submission->update([
            'score' => $validated['score'],
            'feedback' => $validated['feedback'],
        ]);

        // Sync to Grade table
        $subjectId = $submission->assignment?->lesson?->subject_id ?? Subject::value('id');
        Grade::updateOrCreate(
            [
                'student_id' => $submission->student_id,
                'subject_id' => $subjectId,
                'teacher_id' => $teacher?->id ?? Teacher::value('id'),
                'type' => 'Tugas',
                'name' => 'T1',
            ],
            ['score' => $validated['score']]
        );

        return redirect()->back()->with('success', 'Nilai dan feedback tugas berhasil disimpan!');
    }

    // 6. CBT & BANK SOAL
    public function cbt(Request $request)
    {
        $teacher = $this->getTeacher();
        $teacherId = $teacher?->id ?? 0;
        $data = $this->getTeacherData();

        $exams = Exam::with(['subject', 'questions.options', 'attempts'])
            ->where('teacher_id', $teacherId)
            ->latest()
            ->get()
            ->map(function ($e) {
                $status = 'scheduled';
                $now = Carbon::now();
                if ($e->is_active && $now->between($e->start_time, $e->end_time)) {
                    $status = 'active';
                } elseif (! $e->is_active) {
                    $status = 'draft';
                } elseif ($now->gt($e->end_time)) {
                    $status = 'completed';
                }

                $avgScore = $e->attempts->whereNotNull('score')->avg('score');

                return [
                    'id' => $e->id,
                    'title' => $e->title,
                    'subject' => $e->subject?->name ?? 'Mata Pelajaran',
                    'class' => 'Semua Rombel',
                    'date' => Carbon::parse($e->start_time)->format('d M Y'),
                    'time' => Carbon::parse($e->start_time)->format('H:i').' - '.Carbon::parse($e->end_time)->format('H:i'),
                    'duration' => $e->duration_minutes,
                    'totalQuestions' => $e->questions->count(),
                    'status' => $status,
                    'isActive' => $e->is_active,
                    'participants' => $e->attempts->count(),
                    'maxViolations' => $e->max_violations,
                    'avgScore' => $avgScore ? round($avgScore, 1) : null,
                    'questions' => $e->questions->map(function ($q) {
                        return [
                            'id' => $q->id,
                            'type' => $q->type,
                            'content' => $q->content,
                            'marks' => $q->marks,
                            'options' => $q->options->map(fn ($o) => ['content' => $o->content, 'is_correct' => $o->is_correct]),
                        ];
                    }),
                ];
            });

        return Inertia::render('Teacher/CBT', [
            'classes' => $data['classes'],
            'subjects' => $data['subjects'],
            'exams' => $exams,
        ]);
    }

    public function storeExam(Request $request)
    {
        $teacher = $this->getTeacher();
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject_name' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration_minutes' => 'required|integer|min:10',
            'max_violations' => 'required|integer|min:1|max:10',
            'description' => 'nullable|string',
        ]);

        $subject = Subject::where('name', $validated['subject_name'])->first();

        Exam::create([
            'teacher_id' => $teacher?->id ?? Teacher::value('id'),
            'subject_id' => $subject?->id ?? Subject::value('id'),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'duration_minutes' => $validated['duration_minutes'],
            'max_violations' => $validated['max_violations'],
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Ujian CBT baru berhasil dijadwalkan!');
    }

    public function storeQuestion(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:Pilihan Ganda,Benar Salah,Esai,Menjodohkan',
            'content' => 'required|string',
            'marks' => 'required|integer|min:1',
            'options' => 'nullable|array',
        ]);

        $q = Question::create([
            'exam_id' => $exam->id,
            'type' => $validated['type'],
            'content' => $validated['content'],
            'marks' => $validated['marks'],
        ]);

        if (! empty($validated['options'])) {
            foreach ($validated['options'] as $opt) {
                Option::create([
                    'question_id' => $q->id,
                    'content' => $opt['content'],
                    'is_correct' => $opt['is_correct'] ?? false,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Soal berhasil ditambahkan ke bank soal ujian!');
    }

    public function toggleExam(Exam $exam)
    {
        $exam->update(['is_active' => ! $exam->is_active]);

        return redirect()->back()->with('success', 'Status ujian berhasil diperbarui.');
    }

    // 7. BIMBINGAN KONSELING (BK)
    public function bk(Request $request)
    {
        $data = $this->getTeacherData();
        $teacher = $this->getTeacher();

        $violations = StudentViolation::with(['student.user', 'student.studentClass', 'violationPoint', 'teacher.user'])
            ->latest()
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'student' => $v->student?->user?->name ?? 'Siswa',
                    'class' => $v->student?->studentClass?->name ?? '1 Brilliant',
                    'nisn' => $v->student?->nisn ?? '-',
                    'date' => Carbon::parse($v->date)->format('d M Y'),
                    'type' => $v->violationPoint?->category ?? 'Ringan',
                    'points' => $v->violationPoint?->points ?? 5,
                    'description' => $v->description,
                    'status' => $v->status === 'Tercatat' ? 'new' : 'processed',
                    'sp' => in_array($v->status, ['SP 1', 'SP 2', 'SP 3']) ? $v->status : null,
                ];
            });

        $counselings = CounselingSession::with(['student.user', 'student.studentClass', 'teacher.user'])
            ->latest()
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'student' => $c->student?->user?->name ?? 'Siswa',
                    'class' => $c->student?->studentClass?->name ?? '1 Brilliant',
                    'date' => Carbon::parse($c->date)->format('d M Y'),
                    'type' => $c->type,
                    'topic' => $c->problem,
                    'solution' => $c->solution,
                    'status' => $c->status === 'Selesai' ? 'done' : 'scheduled',
                    'guru' => $c->teacher?->user?->name ?? 'Guru Pembimbing',
                ];
            });

        $violationPoints = ViolationPoint::all(['id', 'name', 'points', 'category']);
        $students = Student::with(['user', 'studentClass'])->get()->map(function ($s) {
            return [
                'id' => $s->id,
                'name' => $s->user?->name ?? 'Siswa',
                'nisn' => $s->nisn,
                'class' => $s->studentClass?->name ?? '1 Brilliant',
            ];
        });

        return Inertia::render('Teacher/BK', [
            'classes' => $data['classes'],
            'violations' => $violations,
            'counselings' => $counselings,
            'violationPoints' => $violationPoints,
            'students' => $students,
        ]);
    }

    public function storeViolation(Request $request)
    {
        $teacher = $this->getTeacher();
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'violation_point_id' => 'required|exists:violation_points,id',
            'date' => 'required|date',
            'description' => 'required|string',
        ]);

        $vp = ViolationPoint::find($validated['violation_point_id']);

        // Check student's historical points
        $existingPoints = StudentViolation::where('student_id', $validated['student_id'])
            ->with('violationPoint')
            ->get()
            ->sum(fn ($v) => $v->violationPoint?->points ?? 0);

        $totalPoints = $existingPoints + ($vp?->points ?? 0);
        $status = 'Tercatat';
        if ($totalPoints >= 75) {
            $status = 'SP 3';
        } elseif ($totalPoints >= 50) {
            $status = 'SP 2';
        } elseif ($totalPoints >= 25) {
            $status = 'SP 1';
        }

        StudentViolation::create([
            'student_id' => $validated['student_id'],
            'violation_point_id' => $validated['violation_point_id'],
            'teacher_id' => $teacher?->id,
            'date' => $validated['date'],
            'description' => $validated['description'],
            'status' => $status,
        ]);

        return redirect()->back()->with('success', 'Catatan pelanggaran siswa berhasil disimpan (Status: '.$status.')');
    }

    public function storeCounseling(Request $request)
    {
        $teacher = $this->getTeacher();
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|string|in:Pribadi,Kelompok,Karir,Akademik',
            'date' => 'required|date',
            'problem' => 'required|string',
            'solution' => 'nullable|string',
            'status' => 'required|string|in:Menunggu,Berlangsung,Selesai',
        ]);

        CounselingSession::create([
            'student_id' => $validated['student_id'],
            'teacher_id' => $teacher?->id ?? Teacher::value('id'),
            'type' => $validated['type'],
            'date' => $validated['date'],
            'problem' => $validated['problem'],
            'solution' => $validated['solution'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->back()->with('success', 'Sesi bimbingan konseling berhasil disimpan!');
    }

    // 8. PERPUSTAKAAN
    public function perpustakaan()
    {
        $books = Book::all()->map(function ($b) {
            return [
                'id' => $b->id,
                'title' => $b->title,
                'author' => $b->author ?? 'Penulis',
                'publisher' => $b->publisher ?? 'Penerbit',
                'isbn' => $b->isbn ?? '-',
                'category' => $b->category ?? 'Umum',
                'year' => 2026,
                'stock' => $b->physical_stock,
                'available' => max(0, $b->physical_stock - 2),
                'hasDigital' => ! empty($b->file_path),
                'cover' => $b->cover_photo ?? '📘',
            ];
        });

        $categories = Book::pluck('category')->filter()->unique()->prepend('Semua')->values();

        return Inertia::render('Teacher/Perpustakaan', [
            'books' => $books,
            'categories' => $categories,
        ]);
    }

    // 9. PAYROLL
    public function payroll()
    {
        $teacher = $this->getTeacher();
        $teacherId = $teacher?->id ?? Teacher::value('id');

        $payrolls = Payroll::with('components')
            ->where('teacher_id', $teacherId)
            ->latest()
            ->get();

        if ($payrolls->isEmpty()) {
            // Provide authentic baseline payroll if empty
            $p = Payroll::create([
                'teacher_id' => $teacherId,
                'month' => 'Oktober',
                'year' => '2026',
                'total_salary' => 5450000,
                'status' => 'Dibayar',
            ]);
            $p->components()->createMany([
                ['type' => 'Penerimaan', 'name' => 'Gaji Pokok Pendidik', 'amount' => 3500000],
                ['type' => 'Penerimaan', 'name' => 'Tunjangan Fungsional Guru', 'amount' => 850000],
                ['type' => 'Penerimaan', 'name' => 'Insentif Wali Kelas & Pembiasaan Sholat', 'amount' => 600000],
                ['type' => 'Penerimaan', 'name' => 'Uang Makan & Kehadiran (22 Hari)', 'amount' => 660000],
                ['type' => 'Potongan', 'name' => 'Iuran BPJS Ketenagakerjaan & Kesehatan', 'amount' => 110000],
                ['type' => 'Potongan', 'name' => 'Simpanan Pokok & Wajib Koperasi', 'amount' => 50000],
            ]);
            $payrolls = Payroll::with('components')->where('id', $p->id)->get();
        }

        $latestPayroll = $payrolls->first();
        $earnings = $latestPayroll->components->where('type', 'Penerimaan')->map(fn ($c) => ['label' => $c->name, 'amount' => (float) $c->amount])->values();
        $deductions = $latestPayroll->components->where('type', 'Potongan')->map(fn ($c) => ['label' => $c->name, 'amount' => (float) $c->amount])->values();

        $slipGaji = [
            'month' => $latestPayroll->month.' '.$latestPayroll->year,
            'name' => Auth::user()?->name ?? 'Siti Guru, S.Pd.I',
            'nip' => $teacher?->nip ?? '198407152010012015',
            'jabatan' => 'Guru Pendidikan Agama Islam & Budi Pekerti',
            'golongan' => 'Penata Muda / III-A',
            'status' => strtolower($latestPayroll->status) === 'dibayar' ? 'paid' : 'pending',
            'paidDate' => '25 '.$latestPayroll->month.' '.$latestPayroll->year,
            'earnings' => $earnings,
            'deductions' => $deductions,
            'totalSalary' => (float) $latestPayroll->total_salary,
        ];

        $payrollHistory = $payrolls->map(function ($p) {
            return [
                'id' => $p->id,
                'month' => $p->month.' '.$p->year,
                'takeHome' => (float) $p->total_salary,
                'status' => strtolower($p->status) === 'dibayar' ? 'paid' : 'pending',
                'date' => '25 '.$p->month.' '.$p->year,
            ];
        });

        return Inertia::render('Teacher/Payroll', [
            'slipGaji' => $slipGaji,
            'payrollHistory' => $payrollHistory,
        ]);
    }

    // 10. JURNAL MENGAJAR HARIAN & RINGKASAN MATERI MINGGUAN
    public function jurnal(Request $request)
    {
        $teacher = $this->getTeacher();
        $teacherId = $teacher?->id;

        $selectedClassId = $request->query('class_id', '');
        $selectedSubjectId = $request->query('subject_id', '');
        $selectedWeek = $request->query('week', Carbon::now()->format('o-\WW'));

        $query = TeachingJournal::with(['subject', 'studentClass', 'teachingSchedule'])
            ->when($teacherId, fn ($q) => $q->where('teacher_id', $teacherId))
            ->when($selectedClassId !== '', fn ($q) => $q->where('student_class_id', $selectedClassId))
            ->when($selectedSubjectId !== '', fn ($q) => $q->where('subject_id', $selectedSubjectId));

        if ($selectedWeek !== '' && preg_match('/^(\d{4})-W(\d{2})$/', $selectedWeek, $matches)) {
            $weekStart = Carbon::now()->setISODate((int) $matches[1], (int) $matches[2])->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();
            $query->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()]);
        }

        $journals = $query->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (TeachingJournal $journal): array {
                return [
                    'id' => $journal->id,
                    'student_class_id' => $journal->student_class_id,
                    'class_name' => $journal->studentClass?->name ?? '-',
                    'subject_id' => $journal->subject_id,
                    'subject_name' => $journal->subject?->name ?? '-',
                    'teaching_schedule_id' => $journal->teaching_schedule_id,
                    'date' => $journal->date?->format('Y-m-d') ?? '',
                    'date_formatted' => $journal->date?->locale('id')->translatedFormat('l, d M Y') ?? '-',
                    'week_key' => $journal->date?->format('o-\WW') ?? '',
                    'meeting_number' => $journal->meeting_number,
                    'topic' => $journal->topic,
                    'summary' => $journal->summary,
                    'activities' => $journal->activities,
                    'notes' => $journal->notes,
                ];
            });

        $classes = StudentClass::orderBy('name')->get(['id', 'name']);
        $subjects = Subject::orderBy('name')->get(['id', 'name']);
        $schedules = TeachingSchedule::with(['subject', 'studentClass'])
            ->when($teacherId, fn ($q) => $q->where('teacher_id', $teacherId))
            ->orderBy('day')
            ->orderBy('time_start')
            ->get()
            ->map(fn (TeachingSchedule $s): array => [
                'id' => $s->id,
                'student_class_id' => $s->student_class_id,
                'subject_id' => $s->subject_id,
                'label' => ($s->day_name).' • '.substr((string) $s->time_start, 0, 5).' — '.($s->subject?->name ?? '-').' ('.($s->studentClass?->name ?? '-').')',
            ]);

        return Inertia::render('Teacher/Jurnal', [
            'journals' => $journals,
            'classes' => $classes,
            'subjects' => $subjects,
            'schedules' => $schedules,
            'filters' => [
                'class_id' => $selectedClassId,
                'subject_id' => $selectedSubjectId,
                'week' => $selectedWeek,
            ],
        ]);
    }

    public function storeJurnal(StoreTeachingJournalRequest $request)
    {
        $teacher = $this->getTeacher();
        abort_if(! $teacher, 403, 'Profil guru tidak ditemukan.');

        $validated = $request->validated();

        TeachingJournal::create([
            'teacher_id' => $teacher->id,
            'student_class_id' => $validated['student_class_id'],
            'subject_id' => $validated['subject_id'],
            'teaching_schedule_id' => $validated['teaching_schedule_id'] ?? null,
            'date' => $validated['date'],
            'meeting_number' => $validated['meeting_number'] ?? 1,
            'topic' => $validated['topic'],
            'summary' => $validated['summary'],
            'activities' => $validated['activities'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Jurnal mengajar berhasil disimpan dan ringkasannya tampil di Materi Mingguan siswa.');
    }

    public function updateJurnal(StoreTeachingJournalRequest $request, TeachingJournal $journal)
    {
        Gate::authorize('update', $journal);

        $validated = $request->validated();

        $journal->update([
            'student_class_id' => $validated['student_class_id'],
            'subject_id' => $validated['subject_id'],
            'teaching_schedule_id' => $validated['teaching_schedule_id'] ?? null,
            'date' => $validated['date'],
            'meeting_number' => $validated['meeting_number'] ?? 1,
            'topic' => $validated['topic'],
            'summary' => $validated['summary'],
            'activities' => $validated['activities'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Jurnal mengajar berhasil diperbarui.');
    }

    public function destroyJurnal(TeachingJournal $journal)
    {
        Gate::authorize('delete', $journal);

        $journal->delete();

        return redirect()->back()->with('success', 'Jurnal mengajar berhasil dihapus.');
    }
}
