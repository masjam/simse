<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

abstract class BaseStudentController extends Controller
{
    protected function resolveStudent(): Student
    {
        $relations = [
            'user',
            'studentClass.tingkat',
            'studentClass.teacher.user',
            'studentClass.academicYear',
        ];

        $student = Student::with($relations)
            ->where('user_id', Auth::id())
            ->first();

        if (! $student && Auth::user()?->hasRole('Super Admin')) {
            $student = Student::with($relations)->first();
        }

        abort_if(! $student, 403, 'Profil siswa belum terhubung dengan akun ini.');

        return $student;
    }

    protected function formatStudentHeader(Student $student): array
    {
        return [
            'id' => $student->id,
            'name' => $student->user?->name ?? 'Siswa',
            'nisn' => $student->nisn ?? '-',
            'class_name' => $student->studentClass?->name ?? '-',
            'tingkat' => $student->studentClass?->tingkat?->name ?? ($student->studentClass?->level ?? '-'),
            'wali_kelas' => $student->studentClass?->teacher?->user?->name ?? '-',
        ];
    }
}
