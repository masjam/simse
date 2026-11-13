<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingSchedule;
use Illuminate\Support\Facades\Auth;

abstract class BaseTeacherController extends Controller
{
    protected function getTeacher(): ?Teacher
    {
        $teacher = Teacher::where('user_id', Auth::id())->first();
        if (! $teacher && Auth::user()?->hasRole('Super Admin')) {
            $teacher = Teacher::first();
        }

        return $teacher;
    }

    protected function getTeacherData(): array
    {
        $teacher = $this->getTeacher();
        if (! $teacher) {
            $classes = StudentClass::pluck('name')->values()->toArray();
            $subjects = Subject::pluck('name')->values()->toArray();

            return ['classes' => $classes, 'subjects' => $subjects];
        }

        $classes = TeachingSchedule::with('studentClass')
            ->where('teacher_id', $teacher->id)
            ->get()
            ->pluck('studentClass.name')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($classes)) {
            $classes = StudentClass::pluck('name')->values()->toArray();
        }

        $subjects = TeachingSchedule::with('subject')
            ->where('teacher_id', $teacher->id)
            ->get()
            ->pluck('subject.name')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($subjects)) {
            $subjects = Subject::pluck('name')->values()->toArray();
        }

        return ['classes' => $classes, 'subjects' => $subjects];
    }
}
