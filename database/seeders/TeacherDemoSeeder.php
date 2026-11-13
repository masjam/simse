<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingSchedule;
use App\Models\Tingkat;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Dapatkan atau buat akun Guru Pengajar Demo
        $teacherUser = User::firstOrCreate(
            ['email' => 'guru@sekolah.com'],
            [
                'name' => 'Siti Guru, S.Pd.I.',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if (! $teacherUser->hasRole('Guru Pengajar')) {
            $teacherUser->assignRole('Guru Pengajar');
        }

        // 2. Data Guru
        $teacher = Teacher::firstOrCreate(
            ['user_id' => $teacherUser->id],
            ['nip' => '198407152010012015']
        );

        // 3. Tahun Ajaran 2026/2027 Aktif
        $academicYear = AcademicYear::firstOrCreate(
            ['name' => '2026/2027', 'semester' => 'Ganjil'],
            ['is_active' => true]
        );

        // 4. Struktur SD: Tingkat 1 - 6 & Rombel 1A - 6B
        $classes = [];
        for ($i = 1; $i <= 6; $i++) {
            $tingkat = Tingkat::firstOrCreate(['name' => "Tingkat {$i}"]);
            foreach (['A', 'B'] as $sec) {
                $className = "{$i}{$sec}";
                $classes[$className] = StudentClass::firstOrCreate(
                    ['name' => $className],
                    [
                        'level' => (string) $i,
                        'academic_year_id' => $academicYear->id,
                        'tingkat_id' => $tingkat->id,
                    ]
                );
            }
        }

        $class1A = $classes['1A'];

        // 5. Data Siswa SD Kelas 1A
        $dummyStudents = [
            ['name' => 'Ahmad Fatih Pratama', 'nisn' => '0141234561', 'nik' => '3403010101190001', 'email' => 'ahmad@siswa.com'],
            ['name' => 'Aisyah Putri Kemala', 'nisn' => '0141234562', 'nik' => '3403010101190002', 'email' => 'aisyah@siswa.com'],
            ['name' => 'Bagas Arya Pradipta', 'nisn' => '0141234563', 'nik' => '3403010101190003', 'email' => 'bagas@siswa.com'],
            ['name' => 'Citra Lestari Ramadhani', 'nisn' => '0141234564', 'nik' => '3403010101190004', 'email' => 'citra@siswa.com'],
            ['name' => 'Deni Rizky Pratama', 'nisn' => '0141234565', 'nik' => '3403010101190005', 'email' => 'deni@siswa.com'],
        ];

        foreach ($dummyStudents as $sData) {
            $studentUser = User::firstOrCreate(
                ['email' => $sData['email']],
                [
                    'name' => $sData['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            if (! $studentUser->hasRole('Siswa')) {
                $studentUser->assignRole('Siswa');
            }

            $student = Student::firstOrCreate(
                ['nisn' => $sData['nisn']],
                [
                    'user_id' => $studentUser->id,
                    'student_class_id' => $class1A->id,
                    'nik' => $sData['nik'],
                    'tempat_lahir' => 'Gunungkidul',
                    'tanggal_lahir' => '2019-05-15',
                    'alamat_jalan' => 'Wonosari, Gunungkidul',
                    'no_telp_ortu' => '081234567890',
                ]
            );

            Wallet::firstOrCreate(
                ['student_id' => $student->id],
                ['balance' => 50000, 'pin' => '123456']
            );
        }

        // 6. Mata Pelajaran SD
        $pai = Subject::firstOrCreate(['code' => 'PAI'], ['name' => 'Pendidikan Agama Islam']);
        $mtk = Subject::firstOrCreate(['code' => 'MTK'], ['name' => 'Matematika']);

        // 7. Jadwal Mengajar Guru di Kelas 1A
        TeachingSchedule::firstOrCreate([
            'teacher_id' => $teacher->id,
            'subject_id' => $pai->id,
            'student_class_id' => $class1A->id,
            'day' => 1, // Senin
            'time_start' => '07:30:00',
            'time_end' => '09:00:00',
            'room' => 'Ruang Kelas 1A',
        ]);

        TeachingSchedule::firstOrCreate([
            'teacher_id' => $teacher->id,
            'subject_id' => $mtk->id,
            'student_class_id' => $class1A->id,
            'day' => 2, // Selasa
            'time_start' => '09:30:00',
            'time_end' => '11:00:00',
            'room' => 'Ruang Kelas 1A',
        ]);
    }
}
