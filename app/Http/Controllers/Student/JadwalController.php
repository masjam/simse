<?php

namespace App\Http\Controllers\Student;

use App\Models\TeachingSchedule;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class JadwalController extends BaseStudentController
{
    public function index(): Response
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
}
