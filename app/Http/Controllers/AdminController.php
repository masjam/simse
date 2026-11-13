<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Imports\UsersImport;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'users_count' => User::count(),
                'teachers_count' => Teacher::count(),
                'students_count' => Student::count(),
            ],
        ]);
    }

    public function teachers(Request $request)
    {
        $search = $request->query('search', '');

        $query = User::with(['roles'])->when($search, function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
        })->whereHas('roles', function ($q) {
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

        $query = User::with(['roles'])->when($search, function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
        })->whereHas('roles', function ($q) {
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
        return redirect()->route('sistem.pengaturan');
    }

    public function modules()
    {
        return redirect()->route('sistem.pengaturan');
    }

    public function exportUsers(Request $request)
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }

    public function importUsers(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        Excel::import(new UsersImport, $request->file('file'));

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
