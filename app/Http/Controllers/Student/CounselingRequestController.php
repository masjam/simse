<?php

namespace App\Http\Controllers\Student;

use App\Models\CounselingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CounselingRequestController extends BaseStudentController
{
    /**
     * Simpan permohonan konseling dari siswa kepada Guru BK.
     */
    public function store(Request $request): RedirectResponse
    {
        $student = $this->resolveStudent();

        $validated = $request->validate([
            'category' => 'required|string|in:Belajar,Teman Sebaya,Keluarga,Kebiasaan,Lainnya',
            'preferred_schedule' => 'nullable|string|max:150',
            'notes' => 'required|string|min:5|max:1000',
        ], [
            'category.required' => 'Silakan pilih hal yang ingin diceritakan.',
            'category.in' => 'Kategori yang dipilih tidak valid.',
            'notes.required' => 'Tuliskan sedikit pesan atau hal yang ingin diobrolkan bersama Guru BK.',
            'notes.min' => 'Pesan minimal 5 karakter.',
            'notes.max' => 'Pesan maksimal 1000 karakter.',
        ]);

        CounselingRequest::create([
            'student_id' => $student->id,
            'category' => $validated['category'],
            'preferred_schedule' => $validated['preferred_schedule'] ?? 'Waktu istirahat atau jam luang sekolah',
            'notes' => $validated['notes'],
            'status' => 'Diajukan',
        ]);

        return back()->with('success', 'Pesanmu sudah terkirim ke Guru BK. Ustadz/Ustadzah akan segera mengatur waktu yang nyaman untuk mengobrol bersama.');
    }
}
