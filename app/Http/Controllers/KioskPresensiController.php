<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Notifications\SimseNotification;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KioskPresensiController extends Controller
{
    public function index(Request $request): Response
    {
        $device = $request->attributes->get('kiosk_device');

        $recentAttendances = Attendance::with(['user.student.studentClass'])
            ->whereDate('date', today())
            ->whereHas('user', fn ($q) => $q->role('Siswa'))
            ->latest('updated_at')
            ->take(12)
            ->get()
            ->map(fn (Attendance $att): array => [
                'id' => $att->id,
                'student_name' => $att->user?->name ?? 'Siswa',
                'class_name' => $att->user?->student?->studentClass?->name ?? '-',
                'nisn' => $att->user?->student?->nisn ?? '-',
                'status' => $att->status,
                'time_in' => $att->time_in ? Carbon::parse($att->time_in)->format('H:i') : '-',
                'time_out' => $att->time_out ? Carbon::parse($att->time_out)->format('H:i') : null,
            ]);

        return Inertia::render('Kiosk/Presensi', [
            'device' => [
                'id' => $device?->id,
                'name' => $device?->name ?? 'Kiosk Presensi',
                'mac_address' => $device?->mac_address ?? '-',
                'location' => $device?->location ?? '-',
            ],
            'recentAttendances' => $recentAttendances,
        ]);
    }

    public function tap(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rfid_uid' => ['required', 'string', 'max:100'],
        ], [
            'rfid_uid.required' => 'UID kartu RFID wajib di-tap.',
        ]);

        $uid = trim($validated['rfid_uid']);

        $student = Student::with(['user', 'studentClass', 'parent'])
            ->where('rfid_uid', $uid)
            ->first();

        if (! $student || ! $student->user) {
            return response()->json([
                'message' => "Kartu RFID ({$uid}) belum terdaftar pada data siswa.",
            ], 404);
        }

        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');

        $attendance = Attendance::where('user_id', $student->user_id)
            ->whereDate('date', $today)
            ->first();

        if (! $attendance) {
            $status = $now->format('H:i') > '07:15' ? 'Terlambat' : 'Hadir';
            $attendance = Attendance::create([
                'user_id' => $student->user_id,
                'date' => $today,
                'time_in' => $currentTime,
                'status' => $status,
            ]);

            $mode = 'Masuk';
            $msg = "Ananda {$student->user->name} telah melakukan presensi MASUK pukul {$now->format('H:i')} ({$status}).";
        } elseif (! $attendance->time_out) {
            $attendance->update([
                'time_out' => $currentTime,
            ]);

            $mode = 'Pulang';
            $msg = "Ananda {$student->user->name} telah melakukan presensi PULANG pukul {$now->format('H:i')}.";
        } else {
            return response()->json([
                'status' => 'already_completed',
                'message' => "{$student->user->name} sudah melakukan presensi masuk & pulang hari ini.",
                'record' => [
                    'student_name' => $student->user->name,
                    'class_name' => $student->studentClass?->name ?? '-',
                    'nisn' => $student->nisn,
                    'mode' => 'Lengkap',
                    'attendance_status' => $attendance->status,
                    'time_in' => Carbon::parse($attendance->time_in)->format('H:i'),
                    'time_out' => Carbon::parse($attendance->time_out)->format('H:i'),
                ],
            ]);
        }

        if ($student->no_telp_ortu) {
            app(WhatsAppService::class)->queue(
                phoneNumber: $student->no_telp_ortu,
                message: "Assalamu'alaikum Wr. Wb. {$msg}",
                recipientName: 'Wali '.$student->user->name,
            );
        }

        if ($student->parent) {
            $student->parent->notify(new SimseNotification(
                title: "Presensi {$mode} ({$student->user->name})",
                message: $msg,
                category: 'attendance',
                url: route('ortu.dashboard', ['tab' => 'presensi']),
            ));
        }

        return response()->json([
            'status' => 'ok',
            'message' => $msg,
            'record' => [
                'id' => $attendance->id,
                'student_name' => $student->user->name,
                'class_name' => $student->studentClass?->name ?? '-',
                'nisn' => $student->nisn,
                'mode' => $mode,
                'attendance_status' => $attendance->status,
                'time_in' => $attendance->time_in ? Carbon::parse($attendance->time_in)->format('H:i') : '-',
                'time_out' => $attendance->time_out ? Carbon::parse($attendance->time_out)->format('H:i') : null,
            ],
        ]);
    }
}
