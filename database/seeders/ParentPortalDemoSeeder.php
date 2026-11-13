<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Bill;
use App\Models\BillType;
use App\Models\CounselingSession;
use App\Models\Grade;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Tingkat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ParentPortalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(StudentPortalDemoSeeder::class);

        $parentRole = Role::firstOrCreate(['name' => 'Orang Tua', 'guard_name' => 'web']);
        $siswaRole = Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);

        $parentUser = User::firstOrCreate(
            ['email' => 'ortu@sekolah.com'],
            [
                'name' => 'Bapak Andi',
                'phone' => '6281234567890',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        if (! $parentUser->phone) {
            $parentUser->update(['phone' => '6281234567890']);
        }

        if (! $parentUser->hasRole($parentRole)) {
            $parentUser->assignRole($parentRole);
        }

        $andiUser = User::where('email', 'siswa@sekolah.com')->first();
        $andiStudent = $andiUser ? Student::where('user_id', $andiUser->id)->first() : null;

        if ($andiStudent) {
            $andiStudent->update([
                'parent_user_id' => $parentUser->id,
                'tanggal_lahir' => '2016-05-15',
                'no_telp_ortu' => '081234567890',
                'nama_ayah' => 'Bapak Andi',
                'nama_ibu' => 'Ibu Aminah',
            ]);
        }

        $academicYear = AcademicYear::where('is_active', true)->first()
            ?? AcademicYear::firstOrCreate(['name' => '2026/2027', 'semester' => 'Ganjil'], ['is_active' => true]);

        $tingkat1 = Tingkat::firstOrCreate(['name' => 'Kelas 1']);
        $teacher = Teacher::first();

        $class1A = StudentClass::firstOrCreate(
            ['name' => '1A'],
            [
                'academic_year_id' => $academicYear->id,
                'tingkat_id' => $tingkat1->id,
                'teacher_id' => $teacher?->id,
                'level' => '1',
            ]
        );

        $siblingUser = User::firstOrCreate(
            ['email' => 'aisyah.siswa@sekolah.com'],
            [
                'name' => 'Aisyah Putri',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if (! $siblingUser->hasRole($siswaRole)) {
            $siblingUser->assignRole($siswaRole);
        }

        $siblingStudent = Student::firstOrCreate(
            ['user_id' => $siblingUser->id],
            [
                'student_class_id' => $class1A->id,
                'parent_user_id' => $parentUser->id,
                'nisn' => '0192026002',
                'nik' => '3403012008190002',
                'gender' => 'P',
                'tanggal_lahir' => '2019-08-20',
                'no_telp_ortu' => '081234567890',
                'nama_ayah' => 'Bapak Andi',
                'nama_ibu' => 'Ibu Aminah',
                'qr_code' => 'SIMSE-SISWA-0192026002',
            ]
        );

        $siblingStudent->update([
            'parent_user_id' => $parentUser->id,
            'tanggal_lahir' => '2019-08-20',
            'no_telp_ortu' => '081234567890',
        ]);

        $billType = BillType::firstOrCreate(
            ['name' => 'SPP Bulanan SD'],
            ['amount' => 350000, 'period' => 'Bulanan']
        );

        if ($andiStudent) {
            $paidBill = Bill::firstOrCreate(
                [
                    'student_id' => $andiStudent->id,
                    'bill_type_id' => $billType->id,
                    'month' => 'Oktober',
                    'year' => '2026',
                ],
                [
                    'amount_due' => 350000,
                    'status' => 'Lunas',
                ]
            );

            Payment::firstOrCreate(
                ['bill_id' => $paidBill->id, 'payment_date' => '2026-10-10'],
                [
                    'user_id' => $parentUser->id,
                    'amount_paid' => 350000,
                    'payment_method' => 'Transfer Bank',
                    'status' => 'Berhasil',
                ]
            );

            if ($teacher) {
                CounselingSession::firstOrCreate(
                    [
                        'student_id' => $andiStudent->id,
                        'teacher_id' => $teacher->id,
                        'date' => Carbon::yesterday()->toDateString(),
                    ],
                    [
                        'type' => 'Pribadi',
                        'problem' => 'Beberapa kali hadir mendekati bel masuk pagi.',
                        'solution' => 'Orang tua membantu mengatur jadwal persiapan berangkat sekolah lebih awal.',
                        'status' => 'Selesai',
                    ]
                );
            }
        }

        $subjectPai = Subject::where('code', 'PAI')->first();
        if ($subjectPai && $teacher) {
            Grade::firstOrCreate(
                [
                    'student_id' => $siblingStudent->id,
                    'subject_id' => $subjectPai->id,
                    'teacher_id' => $teacher->id,
                    'type' => 'Tugas',
                    'name' => 'Hafalan Surat Pendek',
                ],
                ['score' => 95]
            );
        }

        Attendance::firstOrCreate(
            ['user_id' => $siblingUser->id, 'date' => Carbon::today()->toDateString()],
            ['status' => 'Hadir', 'time_in' => '06:50:00']
        );

        Bill::firstOrCreate(
            [
                'student_id' => $siblingStudent->id,
                'bill_type_id' => $billType->id,
                'month' => 'November',
                'year' => '2026',
            ],
            [
                'amount_due' => 350000,
                'status' => 'Belum Lunas',
            ]
        );

        if ($andiStudent) {
            StudentAchievement::firstOrCreate(
                [
                    'student_id' => $andiStudent->id,
                    'name' => 'Juara 1 Lomba Tahfidz Juz 30 Tingkat Kabupaten',
                    'year' => 2026,
                ],
                [
                    'category' => 'Keagamaan',
                    'level' => 'Kabupaten/Kota',
                    'organizer' => 'PDM Gunungkidul',
                    'achievement_date' => Carbon::now()->subMonth()->toDateString(),
                    'description' => 'Meraih nilai tertinggi kategori SD Putra.',
                    'status' => 'Disetujui',
                    'submitted_by' => $parentUser->id,
                    'verified_by' => $parentUser->id,
                    'verified_at' => now(),
                    'verification_notes' => 'Sertifikat resmi terverifikasi oleh Guru BK.',
                ]
            );

            StudentAchievement::firstOrCreate(
                [
                    'student_id' => $andiStudent->id,
                    'name' => 'Juara 2 Olimpiade Matematika SD',
                    'year' => 2026,
                ],
                [
                    'category' => 'Akademik',
                    'level' => 'Kecamatan',
                    'organizer' => 'K3S Wonosari',
                    'achievement_date' => Carbon::now()->subDays(5)->toDateString(),
                    'description' => 'Menunggu verifikasi piagam.',
                    'status' => 'Menunggu Verifikasi',
                    'submitted_by' => $parentUser->id,
                ]
            );
        }
    }
}
