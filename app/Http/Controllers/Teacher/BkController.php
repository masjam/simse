<?php

namespace App\Http\Controllers\Teacher;

use App\Models\CounselingSession;
use App\Models\Student;
use App\Models\StudentViolation;
use App\Models\Teacher;
use App\Models\ViolationPoint;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BkController extends BaseTeacherController
{
    public function index(Request $request): Response
    {
        $data = $this->getTeacherData();

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

    public function storeViolation(Request $request): RedirectResponse
    {
        $teacher = $this->getTeacher();
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'violation_point_id' => 'required|exists:violation_points,id',
            'date' => 'required|date',
            'description' => 'required|string',
        ]);

        $vp = ViolationPoint::find($validated['violation_point_id']);

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

    public function storeCounseling(Request $request): RedirectResponse
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
}
