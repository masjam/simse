<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Subject;
use Inertia\Inertia;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::latest()->paginate(15);
        return Inertia::render('Admin/Subject/Index', [
            'subjects' => $subjects
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:subjects,code',
            'name' => 'required|string|max:255',
        ]);

        Subject::create($request->all());

        return back()->with('success', 'Mata Pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $subject = Subject::findOrFail($id);
        
        $request->validate([
            'code' => 'required|string|max:50|unique:subjects,code,' . $id,
            'name' => 'required|string|max:255',
        ]);

        $subject->update($request->all());

        return back()->with('success', 'Mata Pelajaran berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        Subject::findOrFail($id)->delete();
        return back()->with('success', 'Mata Pelajaran berhasil dihapus.');
    }
}
