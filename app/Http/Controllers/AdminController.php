<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;

class AdminController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'users_count' => User::count(),
                'teachers_count' => Teacher::count(),
                'students_count' => Student::count(),
            ]
        ]);
    }

    public function teachers(Request $request)
    {
        $search = $request->query('search', '');
        
        $query = User::with(['roles'])->when($search, function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
        })->whereHas('roles', function($q) {
            $q->whereIn('name', ['Guru Pengajar', 'Guru BK']);
        });

        $users = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/Teachers/Index', [
            'users' => $users,
            'filters' => $request->only(['search']),
        ]);
    }

    public function staffs(Request $request)
    {
        $search = $request->query('search', '');
        
        $query = User::with(['roles'])->when($search, function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
        })->whereHas('roles', function($q) {
            $q->whereNotIn('name', ['Siswa', 'Orang Tua Siswa', 'Guru Pengajar', 'Guru BK']);
        });

        $users = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/Staffs/Index', [
            'users' => $users,
            'filters' => $request->only(['search']),
        ]);
    }

    public function settings()
    {
        return Inertia::render('Admin/Settings');
    }

    public function modules()
    {
        $modules = [
            ['id' => 1, 'name' => 'SIAKAD & Akademik', 'description' => 'Sistem Informasi Akademik, Jadwal, dan Raport', 'icon' => 'akademik', 'status' => true],
            ['id' => 2, 'name' => 'E-Learning (LMS)', 'description' => 'Materi Belajar, Tugas Online, dan Kuis', 'icon' => 'lms', 'status' => true],
            ['id' => 3, 'name' => 'Ujian Online (CBT)', 'description' => 'Ujian dengan Pengawasan Anti-Kecurangan', 'icon' => 'cbt', 'status' => true],
            ['id' => 4, 'name' => 'Bimbingan Konseling', 'description' => 'Pencatatan Poin Pelanggaran dan SP', 'icon' => 'bk', 'status' => true],
            ['id' => 5, 'name' => 'Absensi Digital', 'description' => 'Absensi QR Code dan Face ID', 'icon' => 'absensi', 'status' => true],
            ['id' => 6, 'name' => 'Keuangan & SPP', 'description' => 'Tagihan SPP dan Kasir Pembayaran', 'icon' => 'keuangan', 'status' => true],
            ['id' => 7, 'name' => 'E-Kantin Digital', 'description' => 'Dompet Digital dan Stand Kantin', 'icon' => 'kantin', 'status' => false],
            ['id' => 8, 'name' => 'Payroll Pegawai', 'description' => 'Penggajian, Slip Gaji, dan Kasbon', 'icon' => 'payroll', 'status' => false],
            ['id' => 9, 'name' => 'PPDB Online', 'description' => 'Penerimaan Siswa Baru', 'icon' => 'ppdb', 'status' => false],
            ['id' => 10, 'name' => 'Perpustakaan Digital', 'description' => 'Katalog Buku dan Peminjaman', 'icon' => 'perpustakaan', 'status' => false],
        ];

        return Inertia::render('Admin/Modules', [
            'modules' => $modules
        ]);
    }
    public function exportUsers(Request $request)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\UsersExport, 'users.xlsx');
    }

    public function importUsers(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\UsersImport, $request->file('file'));

        return back()->with('success', 'Data pengguna berhasil diimpor.');
    }

    public function downloadTemplate(Request $request)
    {
        $tab = $request->query('tab', 'pegawai');
        
        return response()->streamDownload(function () use ($tab) {
            if ($tab === 'ortu') {
                echo "name,email,password,role,id_siswa\nBudi (Ortu Andi),budi@example.com,password123,Orang Tua Siswa,1";
            } elseif ($tab === 'siswa') {
                echo "name,email,password,role,tingkat_id,rombel_id,nisn,nik,nama_ayah,nama_ibu,pekerjaan_ayah,pekerjaan_ibu,no_telp_ortu\nAndi Siswa,andi@example.com,password123,Siswa,1,1,1234567890,3201234567890001,Budi,Ani,Wiraswasta,Ibu Rumah Tangga,081234567890";
            } else {
                echo "name,email,password,role\nPegawai A,pegawai@example.com,password123,Staf Sekolah";
            }
        }, "template_import_{$tab}.csv");
    }
}
