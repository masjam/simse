<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Attendance;
use App\Models\Exam;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\TeachingSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends BaseTeacherController
{
    public function index(): Response
    {
        $teacher = $this->getTeacher();
        $teacherId = $teacher?->id ?? 0;

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

        $studentUserIds = Student::whereIn('student_class_id', $classIds)->pluck('user_id');
        $totalAttendances = Attendance::whereIn('user_id', $studentUserIds)->count();
        $presentAttendances = Attendance::whereIn('user_id', $studentUserIds)->where('status', 'Hadir')->count();
        $attendanceRate = $totalAttendances > 0 ? round(($presentAttendances / $totalAttendances) * 100, 1) : 96.4;

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
}
