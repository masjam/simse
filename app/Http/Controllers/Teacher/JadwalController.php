<?php

namespace App\Http\Controllers\Teacher;

use App\Models\TeachingSchedule;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class JadwalController extends BaseTeacherController
{
    public function index(): Response
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
}
