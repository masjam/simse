<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Tingkat;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');

        $query = User::with(['roles', 'student.studentClass.tingkat'])->when($search, function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
        })->whereHas('roles', function ($q) {
            $q->where('name', 'Siswa');
        });

        if ($request->filled('tingkat_id')) {
            $query->whereHas('student.studentClass', function ($q) use ($request) {
                $q->where('tingkat_id', $request->tingkat_id);
            });
        }
        if ($request->filled('rombel_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('student_class_id', $request->rombel_id);
            });
        }

        $users = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/Students/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'tingkat_id', 'rombel_id']),
            'tingkats' => Tingkat::all(),
            'rombels' => StudentClass::all(),
        ]);
    }

    public function edit($id)
    {
        // the ID passed is actually the User ID from the users table.
        // We need to fetch the User and the associated Student profile.
        $user = User::with(['student.achievements', 'student.periodics', 'student.studentClass'])->findOrFail($id);

        if (! $user->hasRole('Siswa')) {
            return redirect()->route('admin.users')->with('error', 'User bukan siswa.');
        }

        // If student profile doesn't exist yet, we can create an empty one
        if (! $user->student) {
            $class = StudentClass::first();
            if (! $class) {
                return redirect()->route('admin.users')->with('error', 'Buat Kelas/Rombel terlebih dahulu.');
            }
            $user->student()->create([
                'student_class_id' => $class->id,
                'nisn' => 'TBD-'.time(),
                'nik' => 'TBD-'.time(),
            ]);
            $user->load('student');
        }

        return Inertia::render('Admin/Students/Edit', [
            'studentUser' => $user,
            'tingkats' => Tingkat::all(),
            'studentClasses' => StudentClass::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $student = $user->student;

        if (! $student) {
            return back()->with('error', 'Profil siswa tidak ditemukan.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            // we can validate more fields if needed
        ]);

        // Update User
        $user->update([
            'name' => $request->name,
        ]);

        // Update Student Profile
        $student->update($request->only([
            'student_class_id', 'gender', 'nisn', 'kewarganegaraan', 'nik', 'no_kk', 'tempat_lahir', 'tanggal_lahir',
            'no_registrasi_akta_lahir', 'berkebutuhan_khusus', 'agama', 'alamat_jalan', 'rt', 'rw',
            'dusun', 'desa_kelurahan', 'kode_pos', 'lintang', 'bujur', 'tempat_tinggal',
            'moda_transportasi', 'anak_ke',
            'nik_ayah', 'tahun_lahir_ayah', 'pendidikan_ayah', 'berkebutuhan_khusus_ayah', 'penghasilan_ayah',
            'status_hidup_ibu', 'nik_ibu', 'tahun_lahir_ibu', 'pendidikan_ibu', 'berkebutuhan_khusus_ibu', 'penghasilan_ibu',
            'mempunyai_wali', 'nama_wali', 'nik_wali', 'tahun_lahir_wali', 'pendidikan_wali', 'pekerjaan_wali', 'penghasilan_wali',
            'no_telepon_rumah', 'no_hp',
        ]));

        return back()->with('success', 'Data peserta didik berhasil diperbarui.');
    }

    public function storeAchievement(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if (! $user->student) {
            return back()->with('error', 'Siswa tidak ditemukan.');
        }

        $request->validate([
            'name' => 'required|string',
            'level' => 'required|string',
            'year' => 'required|integer',
        ]);

        $user->student->achievements()->create($request->all());

        return back()->with('success', 'Prestasi berhasil ditambahkan.');
    }

    public function storePeriodic(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if (! $user->student) {
            return back()->with('error', 'Siswa tidak ditemukan.');
        }

        $request->validate([
            'tinggi_badan' => 'nullable|numeric',
            'berat_badan' => 'nullable|numeric',
            'lingkar_kepala' => 'nullable|numeric',
            'recorded_at' => 'nullable|date',
        ]);

        $user->student->periodics()->create($request->all());

        return back()->with('success', 'Data periodik berhasil ditambahkan.');
    }

    public function print($id)
    {
        $user = User::with(['student.achievements', 'student.periodics', 'student.studentClass.tingkat'])->findOrFail($id);
        if (! $user->hasRole('Siswa')) {
            abort(404, 'Data bukan siswa.');
        }

        return view('print.student', compact('user'));
    }
}
