<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeachingSchedule;
use App\Models\Teacher;
use App\Models\StudentClass;
use App\Models\Subject;
use Inertia\Inertia;

class TeachingAssignmentController extends Controller
{
    public function index()
    {
        $assignments = TeachingSchedule::with(['teacher.user', 'studentClass', 'subject'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/TeachingAssignment/Index', [
            'assignments' => $assignments,
            'teachers' => Teacher::with('user')->get(),
            'student_classes' => StudentClass::all(),
            'subjects' => Subject::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'subject_id' => 'required|exists:subjects,id',
            'student_class_ids' => 'required|array|min:1',
            'student_class_ids.*' => 'exists:student_classes,id',
            'jpl' => 'required|integer|min:1|max:40',
        ]);

        $addedCount = 0;
        $existCount = 0;

        foreach ($request->student_class_ids as $class_id) {
            $exists = TeachingSchedule::where('teacher_id', $request->teacher_id)
                ->where('subject_id', $request->subject_id)
                ->where('student_class_id', $class_id)
                ->exists();

            if ($exists) {
                $existCount++;
            } else {
                TeachingSchedule::create([
                    'teacher_id' => $request->teacher_id,
                    'subject_id' => $request->subject_id,
                    'student_class_id' => $class_id,
                    'jpl' => $request->jpl
                ]);
                $addedCount++;
            }
        }

        if ($addedCount > 0) {
            $msg = $addedCount . ' tugas mengajar berhasil ditambahkan.';
            if ($existCount > 0) {
                $msg .= ' (' . $existCount . ' dilewati karena sudah ada)';
            }
            return back()->with('success', $msg);
        }

        return back()->withErrors(['message' => 'Semua tugas mengajar tersebut sudah ada sebelumnya.']);
    }

    public function update(Request $request, $id)
    {
        $assignment = TeachingSchedule::findOrFail($id);

        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'subject_id' => 'required|exists:subjects,id',
            'student_class_id' => 'required|exists:student_classes,id',
            'jpl' => 'required|integer|min:1|max:40',
        ]);

        // Validate if this exact assignment already exists on another record
        $exists = TeachingSchedule::where('teacher_id', $request->teacher_id)
            ->where('subject_id', $request->subject_id)
            ->where('student_class_id', $request->student_class_id)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['message' => 'Tugas mengajar ini sudah ada.']);
        }

        $assignment->update([
            'teacher_id' => $request->teacher_id,
            'subject_id' => $request->subject_id,
            'student_class_id' => $request->student_class_id,
            'jpl' => $request->jpl
        ]);

        return back()->with('success', 'Tugas mengajar berhasil diperbarui.');
    }

    public function destroy($id)
    {
        TeachingSchedule::findOrFail($id)->delete();
        return back()->with('success', 'Tugas mengajar berhasil dihapus.');
    }
}
