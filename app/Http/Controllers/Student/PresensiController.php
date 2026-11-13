<?php

namespace App\Http\Controllers\Student;

use App\Models\Attendance;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class PresensiController extends BaseStudentController
{
    public function index(): Response
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
}
