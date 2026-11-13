<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\StudentClass;
use App\Models\Teacher;
use App\Models\Tingkat;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentClassController extends Controller
{
    public function index()
    {
        $rombels = StudentClass::with(['tingkat', 'academicYear', 'teacher.user'])->latest()->paginate(10);

        return Inertia::render('Admin/StudentClass/Index', [
            'rombels' => $rombels,
            'tingkats' => Tingkat::all(),
            'academic_years' => AcademicYear::all(),
            'teachers' => Teacher::with('user')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|string',
            'tingkat_id' => 'required|exists:tingkats,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);

        StudentClass::create($request->all());

        return back()->with('success', 'Rombel berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $rombel = StudentClass::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|string',
            'tingkat_id' => 'required|exists:tingkats,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $rombel->update($request->all());

        return back()->with('success', 'Rombel berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        StudentClass::findOrFail($id)->delete();

        return back()->with('success', 'Rombel berhasil dihapus.');
    }
}
