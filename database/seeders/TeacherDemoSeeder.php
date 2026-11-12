<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Subject;
use App\Models\StudentClass;
use App\Models\TeachingSchedule;
use Illuminate\Support\Facades\DB;

class TeacherDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get the "Siti Guru" teacher user from UserRoleSeeder
        $teacherUser = User::where('email', 'guru@sekolah.com')->first();
        if (!$teacherUser) return;

        // 2. Create the Teacher record
        $teacher = Teacher::firstOrCreate(
            ['user_id' => $teacherUser->id],
            ['nip' => '198001012010012001']
        );

        // 3. Create dummy Academic Year & Major just to satisfy constraints
        $academicYearId = DB::table('academic_years')->insertGetId([
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
            'created_at' => now(), 'updated_at' => now()
        ]);
        
        $majorId = DB::table('majors')->insertGetId([
            'code' => 'RPL', 'name' => 'Rekayasa Perangkat Lunak',
            'created_at' => now(), 'updated_at' => now()
        ]);

        // 4. Create a Student Class (e.g., 11 RPL 1)
        $studentClass = StudentClass::firstOrCreate(
            ['name' => '11 RPL 1'],
            ['level' => '11', 'major_id' => $majorId, 'academic_year_id' => $academicYearId]
        );

        // 5. Create Students in that class
        $dummyStudents = [
            ['name' => 'Ahmad Budi Santoso', 'nisn' => '0041234561', 'nik' => '3201010101010001', 'email' => 'ahmad@siswa.com'],
            ['name' => 'Aisyah Putri Kemala', 'nisn' => '0041234562', 'nik' => '3201010101010002', 'email' => 'aisyah@siswa.com'],
            ['name' => 'Bagas Arya Pradipta', 'nisn' => '0041234563', 'nik' => '3201010101010003', 'email' => 'bagas@siswa.com'],
            ['name' => 'Citra Lestari', 'nisn' => '0041234564', 'nik' => '3201010101010004', 'email' => 'citra@siswa.com'],
            ['name' => 'Deni Ramadhan', 'nisn' => '0041234565', 'nik' => '3201010101010005', 'email' => 'deni@siswa.com'],
        ];

        foreach ($dummyStudents as $sData) {
            $studentUser = User::firstOrCreate(
                ['email' => $sData['email']],
                ['name' => $sData['name'], 'password' => bcrypt('password')]
            );
            if (!$studentUser->hasRole('Siswa')) $studentUser->assignRole('Siswa');

            Student::firstOrCreate(
                ['nisn' => $sData['nisn']],
                [
                    'user_id' => $studentUser->id,
                    'student_class_id' => $studentClass->id,
                    'nik' => $sData['nik']
                ]
            );
        }

        // 6. Create Subject
        $subject = Subject::firstOrCreate(
            ['code' => 'PWPB'],
            ['name' => 'Pemrograman Web & Perangkat Bergerak']
        );

        // 7. Create Teaching Schedule
        TeachingSchedule::firstOrCreate([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'student_class_id' => $studentClass->id,
            'day' => 1, // Senin
            'time_start' => '07:30:00',
            'time_end' => '09:00:00',
            'room' => 'Lab Komputer 1'
        ]);
        
        TeachingSchedule::firstOrCreate([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'student_class_id' => $studentClass->id,
            'day' => 2, // Selasa
            'time_start' => '10:00:00',
            'time_end' => '12:00:00',
            'room' => 'Lab Komputer 2'
        ]);
    }
}
