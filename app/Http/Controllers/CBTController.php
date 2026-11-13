<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class CBTController extends Controller
{
    public function index()
    {
        // Mock data untuk demonstrasi UI
        $activeExams = [
            [
                'id' => 1,
                'title' => 'Penilaian Akhir Semester (PAS) - Matematika',
                'description' => 'Ujian akhir semester ganjil tahun ajaran 2026/2027.',
                'subject' => 'Matematika Peminatan',
                'duration_minutes' => 90,
                'max_violations' => 3,
                'start_time' => now()->subMinutes(10)->toIso8601String(), // Sedang berlangsung
                'end_time' => now()->addMinutes(80)->toIso8601String(),
                'status' => 'Berlangsung',
            ],
            [
                'id' => 2,
                'title' => 'Kuis Harian - Rekayasa Perangkat Lunak',
                'description' => 'Materi: Pemrograman Berorientasi Objek Dasar (OOP).',
                'subject' => 'Pemrograman Dasar',
                'duration_minutes' => 45,
                'max_violations' => 5,
                'start_time' => now()->addHours(2)->toIso8601String(), // Akan datang
                'end_time' => now()->addHours(3)->toIso8601String(),
                'status' => 'Akan Datang',
            ],
        ];

        return Inertia::render('CBT/Index', [
            'exams' => $activeExams,
        ]);
    }
}
