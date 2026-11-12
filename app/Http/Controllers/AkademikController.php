<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class AkademikController extends Controller
{
    public function absensi()
    {
        // Dummy Data Siswa untuk Kelas yang sedang diajar guru ini
        $students = [
            ['id' => 1, 'nisn' => '0041234561', 'name' => 'Ahmad Budi Santoso', 'gender' => 'L', 'status' => 'Belum Diisi'],
            ['id' => 2, 'nisn' => '0041234562', 'name' => 'Aisyah Putri Kemala', 'gender' => 'P', 'status' => 'Belum Diisi'],
            ['id' => 3, 'nisn' => '0041234563', 'name' => 'Bagas Arya Pradipta', 'gender' => 'L', 'status' => 'Belum Diisi'],
            ['id' => 4, 'nisn' => '0041234564', 'name' => 'Citra Lestari', 'gender' => 'P', 'status' => 'Belum Diisi'],
            ['id' => 5, 'nisn' => '0041234565', 'name' => 'Deni Ramadhan', 'gender' => 'L', 'status' => 'Belum Diisi'],
        ];

        return Inertia::render('Akademik/Absensi', [
            'className' => '11 RPL 1',
            'subjectName' => 'Pemrograman Web & Perangkat Bergerak',
            'scheduleTime' => '07:30 - 09:00',
            'students' => $students
        ]);
    }
}
