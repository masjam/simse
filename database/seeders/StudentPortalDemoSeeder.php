<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Bill;
use App\Models\BillType;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\Lesson;
use App\Models\Option;
use App\Models\Question;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentViolation;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use App\Models\TeachingSchedule;
use App\Models\Tingkat;
use App\Models\User;
use App\Models\ViolationPoint;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StudentPortalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $siswaRole = Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);
        $guruRole = Role::firstOrCreate(['name' => 'Guru Pengajar', 'guard_name' => 'web']);

        $academicYear = AcademicYear::firstOrCreate(
            ['name' => '2026/2027', 'semester' => 'Ganjil'],
            ['is_active' => true]
        );

        $tingkat4 = Tingkat::firstOrCreate(['name' => 'Kelas 4']);

        $teacherUser = User::firstOrCreate(
            ['email' => 'guru@sekolah.com'],
            [
                'name' => 'Siti Guru, S.Pd.I',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if (! $teacherUser->hasRole($guruRole)) {
            $teacherUser->assignRole($guruRole);
        }

        $teacher = Teacher::firstOrCreate(
            ['user_id' => $teacherUser->id],
            ['nip' => '198407152010012015']
        );

        $class4A = StudentClass::firstOrCreate(
            ['name' => '4A'],
            [
                'academic_year_id' => $academicYear->id,
                'tingkat_id' => $tingkat4->id,
                'teacher_id' => $teacher->id,
                'level' => '4',
            ]
        );

        $studentUser = User::firstOrCreate(
            ['email' => 'siswa@sekolah.com'],
            [
                'name' => 'Andi Siswa',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if (! $studentUser->hasRole($siswaRole)) {
            $studentUser->assignRole($siswaRole);
        }

        $student = Student::firstOrCreate(
            ['user_id' => $studentUser->id],
            [
                'student_class_id' => $class4A->id,
                'nisn' => '0152026001',
                'nik' => '3403011505160001',
                'gender' => 'L',
                'qr_code' => 'SIMSE-SISWA-0152026001',
            ]
        );

        $subjectPai = Subject::firstOrCreate(
            ['code' => 'PAI'],
            ['name' => 'Pendidikan Agama Islam dan Budi Pekerti']
        );
        $subjectMtk = Subject::firstOrCreate(
            ['code' => 'MTK'],
            ['name' => 'Matematika']
        );

        $todayIso = Carbon::now()->dayOfWeekIso;
        $activeDay = $todayIso <= 6 ? $todayIso : 1;

        TeachingSchedule::firstOrCreate(
            [
                'teacher_id' => $teacher->id,
                'subject_id' => $subjectPai->id,
                'student_class_id' => $class4A->id,
                'day' => $activeDay,
            ],
            [
                'time_start' => '07:15:00',
                'time_end' => '08:45:00',
                'room' => 'Ruang 4A',
                'jpl' => 2,
            ]
        );

        TeachingSchedule::firstOrCreate(
            [
                'teacher_id' => $teacher->id,
                'subject_id' => $subjectMtk->id,
                'student_class_id' => $class4A->id,
                'day' => $activeDay,
            ],
            [
                'time_start' => '09:00:00',
                'time_end' => '10:30:00',
                'room' => 'Ruang 4A',
                'jpl' => 2,
            ]
        );

        $sampleAttachmentPath = 'materials/demo_materi_akhlak_4a.pdf';
        if (! Storage::disk('local')->exists($sampleAttachmentPath)) {
            Storage::disk('local')->put($sampleAttachmentPath, '%PDF-1.4 Demo Materi SD Muhammadiyah Al Mujahidin Wonosari');
        }

        $lesson = Lesson::firstOrCreate(
            [
                'teacher_id' => $teacher->id,
                'subject_id' => $subjectPai->id,
                'student_class_id' => $class4A->id,
                'title' => 'Bab 1: Teladan Jujur & Amanah dalam Kehidupan Sehari-hari',
            ],
            [
                'content' => 'Siswa mempelajari makna sifat jujur dan amanah di rumah maupun di sekolah beserta contoh penerapannya.',
                'attachment' => $sampleAttachmentPath,
                'order' => 1,
            ]
        );

        Assignment::firstOrCreate(
            [
                'lesson_id' => $lesson->id,
                'title' => 'Latihan Cerita Pengalaman Bersikap Jujur',
            ],
            [
                'description' => 'Tuliskan 1 paragraf pengalaman kamu bersikap jujur di sekolah atau rumah.',
                'due_date' => Carbon::now()->addDays(2),
                'max_score' => 100,
            ]
        );

        Grade::firstOrCreate(
            ['student_id' => $student->id, 'subject_id' => $subjectPai->id, 'teacher_id' => $teacher->id, 'type' => 'Tugas', 'name' => 'Tugas 1'],
            ['score' => 90]
        );
        Grade::firstOrCreate(
            ['student_id' => $student->id, 'subject_id' => $subjectPai->id, 'teacher_id' => $teacher->id, 'type' => 'UTS', 'name' => 'UTS'],
            ['score' => 88]
        );

        Attendance::firstOrCreate(
            ['user_id' => $studentUser->id, 'date' => Carbon::today()->toDateString()],
            ['status' => 'Hadir', 'time_in' => '06:55:00']
        );

        $billType = BillType::firstOrCreate(
            ['name' => 'SPP Bulanan SD'],
            ['amount' => 350000, 'period' => 'Bulanan']
        );

        Bill::firstOrCreate(
            [
                'student_id' => $student->id,
                'bill_type_id' => $billType->id,
                'month' => 'November',
                'year' => '2026',
            ],
            [
                'amount_due' => 350000,
                'status' => 'Belum Lunas',
            ]
        );

        $vp = ViolationPoint::firstOrCreate(
            ['name' => 'Terlambat Datang ke Sekolah'],
            ['points' => 5, 'category' => 'Ringan']
        );

        StudentViolation::firstOrCreate(
            [
                'student_id' => $student->id,
                'violation_point_id' => $vp->id,
                'date' => Carbon::yesterday()->toDateString(),
            ],
            [
                'teacher_id' => $teacher->id,
                'description' => 'Terlambat mengikuti apel pagi 10 menit.',
                'status' => 'Tercatat',
            ]
        );

        $exam = Exam::firstOrCreate(
            [
                'teacher_id' => $teacher->id,
                'subject_id' => $subjectPai->id,
                'title' => 'Latihan Ujian Harian PAI Kelas 4',
            ],
            [
                'description' => 'Ujian online demo untuk Portal Siswa SD Muhammadiyah Al Mujahidin Wonosari.',
                'start_time' => Carbon::now()->subMinutes(15),
                'end_time' => Carbon::now()->addHours(3),
                'duration_minutes' => 45,
                'max_violations' => 3,
                'is_active' => true,
            ]
        );

        $q1 = Question::firstOrCreate(
            ['exam_id' => $exam->id, 'content' => 'Berkata sesuai kenyataan dan tidak berbohong disebut sifat ...'],
            ['type' => 'Pilihan Ganda', 'marks' => 40]
        );
        Option::firstOrCreate(['question_id' => $q1->id, 'content' => 'Jujur (Shiddiq)'], ['is_correct' => true]);
        Option::firstOrCreate(['question_id' => $q1->id, 'content' => 'Kikir'], ['is_correct' => false]);
        Option::firstOrCreate(['question_id' => $q1->id, 'content' => 'Sombong'], ['is_correct' => false]);

        $q2 = Question::firstOrCreate(
            ['exam_id' => $exam->id, 'content' => 'Menjaga barang titipan teman dengan baik merupakan wujud sikap amanah.'],
            ['type' => 'Benar Salah', 'marks' => 30]
        );
        Option::firstOrCreate(['question_id' => $q2->id, 'content' => 'Benar'], ['is_correct' => true]);
        Option::firstOrCreate(['question_id' => $q2->id, 'content' => 'Salah'], ['is_correct' => false]);

        Question::firstOrCreate(
            ['exam_id' => $exam->id, 'content' => 'Berikan satu contoh sikap jujur saat berada di kelas!'],
            ['type' => 'Esai', 'marks' => 30]
        );

        TeachingJournal::firstOrCreate(
            [
                'teacher_id' => $teacher->id,
                'subject_id' => $subjectPai->id,
                'student_class_id' => $class4A->id,
                'topic' => 'Makna Jujur dan Amanah di Sekolah',
                'date' => Carbon::now()->startOfWeek()->addDays(1)->toDateString(),
            ],
            [
                'meeting_number' => 1,
                'summary' => 'Siswa mempelajari pengertian sifat jujur (Shiddiq) dan amanah, serta menuliskan contoh perilaku jujur saat mengerjakan ujian dan menjaga barang titipan teman.',
                'activities' => 'Bercerita kisah teladan Rasulullah SAW dan diskusi kelompok kecil.',
                'notes' => 'Baca kembali modul Bab 1 untuk persiapan latihan pekan depan.',
            ]
        );

        TeachingJournal::firstOrCreate(
            [
                'teacher_id' => $teacher->id,
                'subject_id' => $subjectMtk->id,
                'student_class_id' => $class4A->id,
                'topic' => 'Perkalian Bilangan Cacah Dua Angka',
                'date' => Carbon::now()->startOfWeek()->addDays(2)->toDateString(),
            ],
            [
                'meeting_number' => 2,
                'summary' => 'Membahas langkah perkalian bersusun dua angka dan penyelesaian soal cerita sederhana tentang pembagian buku tulis.',
                'activities' => 'Latihan bersama di papan tulis dan kuis berpasangan.',
                'notes' => 'Kerjakan 5 soal latihan halaman 32 bersama orang tua di rumah.',
            ]
        );

        TeachingJournal::firstOrCreate(
            [
                'teacher_id' => $teacher->id,
                'subject_id' => $subjectPai->id,
                'student_class_id' => $class4A->id,
                'topic' => 'Membaca Surat Al-Maun dengan Tartil',
                'date' => Carbon::now()->subWeek()->startOfWeek()->addDays(2)->toDateString(),
            ],
            [
                'meeting_number' => 1,
                'summary' => 'Mengulang bacaan QS. Al-Maun ayat 1-7 dengan memperhatikan hukum bacaan mad dan ghunnah.',
                'activities' => 'Talaqqi dan menyimak bacaan teman sebangku.',
            ]
        );
    }
}
