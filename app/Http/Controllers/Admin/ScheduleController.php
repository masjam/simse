<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeachingSchedule;
use App\Models\StudentClass;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $class_id = $request->query('class_id');
        
        $schedules = TeachingSchedule::with(['teacher.user', 'studentClass', 'subject'])
            ->when($class_id, function ($query, $class_id) {
                return $query->where('student_class_id', $class_id);
            })
            ->orderBy('day')
            ->orderBy('time_start')
            ->paginate(20);

        return Inertia::render('Admin/Schedule/Index', [
            'schedules' => $schedules,
            'student_classes' => StudentClass::all(),
            'current_class_id' => $class_id,
        ]);
    }

    public function update(Request $request, $id)
    {
        $schedule = TeachingSchedule::findOrFail($id);

        $request->validate([
            'day' => 'required|integer|min:1|max:6',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
            'room' => 'nullable|string|max:100',
        ]);

        $schedule->update([
            'day' => $request->day,
            'time_start' => $request->time_start,
            'time_end' => $request->time_end,
            'room' => $request->room,
        ]);

        return back()->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function autoGenerate()
    {
        $unassigned = TeachingSchedule::whereNull('day')->orWhereNull('time_start')->get();
        if ($unassigned->isEmpty()) {
            return back()->with('success', 'Semua penugasan sudah memiliki jadwal.');
        }

        $timeSlots = [
            ['start' => '07:00', 'end' => '08:30'],
            ['start' => '08:30', 'end' => '10:00'],
            ['start' => '10:30', 'end' => '12:00'],
            ['start' => '13:00', 'end' => '14:30'],
        ];

        $days = [1, 2, 3, 4, 5]; // Senin - Jumat
        $generatedCount = 0;

        foreach ($unassigned as $assignment) {
            $assigned = false;
            foreach ($days as $day) {
                if ($assigned) break;
                foreach ($timeSlots as $slot) {
                    // Check if teacher is busy
                    $teacherBusy = TeachingSchedule::where('teacher_id', $assignment->teacher_id)
                        ->where('day', $day)
                        ->where('time_start', $slot['start'])
                        ->exists();

                    // Check if class is busy
                    $classBusy = TeachingSchedule::where('student_class_id', $assignment->student_class_id)
                        ->where('day', $day)
                        ->where('time_start', $slot['start'])
                        ->exists();

                    if (!$teacherBusy && !$classBusy) {
                        $assignment->update([
                            'day' => $day,
                            'time_start' => $slot['start'],
                            'time_end' => $slot['end'],
                        ]);
                        $assigned = true;
                        $generatedCount++;
                        break;
                    }
                }
            }
        }

        return back()->with('success', $generatedCount . ' jadwal berhasil digenerate otomatis tanpa bentrok.');
    }
}
