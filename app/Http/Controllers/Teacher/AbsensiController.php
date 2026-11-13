<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\StudentClass;
use App\Notifications\SimseNotification;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AbsensiController extends BaseTeacherController
{
    public function index(Request $request): Response
    {
        $data = $this->getTeacherData();
        $classes = $data['classes'];
        $selectedClass = $request->query('class', $classes[0] ?? '1 Brilliant');
        $selectedDate = $request->query('date', Carbon::today()->toDateString());

        $studentClass = StudentClass::where('name', $selectedClass)->first();

        $students = collect();
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

    public function store(Request $request): RedirectResponse
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
}
