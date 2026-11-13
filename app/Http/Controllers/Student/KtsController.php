<?php

namespace App\Http\Controllers\Student;

use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class KtsController extends BaseStudentController
{
    public function index(): Response
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
}
