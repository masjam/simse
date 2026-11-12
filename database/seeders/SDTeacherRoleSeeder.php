<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Book;
use App\Models\CounselingSession;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\Lesson;
use App\Models\Option;
use App\Models\Payroll;
use App\Models\PayrollComponent;
use App\Models\Question;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentViolation;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\Teacher;
use App\Models\TeachingSchedule;
use App\Models\User;
use App\Models\ViolationPoint;
use App\Models\WhatsappLog;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SDTeacherRoleSeeder extends Seeder
{
    public function run(): void
    {
        $teacherUser = User::where('email', 'guru@sekolah.com')->first();
        if (! $teacherUser) {
            return;
        }

        $teacher = Teacher::firstOrCreate(
            ['user_id' => $teacherUser->id],
            ['nip' => '198407152010012015']
        );

        $classes = StudentClass::all();
        $paiSubject = Subject::where('code', 'PAI')->first() ?? Subject::first();
        $binSubject = Subject::where('code', 'BIN')->first() ?? Subject::skip(1)->first();
        $students = Student::with('user')->get();

        if ($classes->isEmpty() || $students->isEmpty() || ! $paiSubject) {
            return;
        }

        $class1 = $classes->first();
        $class2 = $classes->count() > 1 ? $classes->skip(1)->first() : $class1;

        // 1. Ensure Teaching Schedules
        TeachingSchedule::firstOrCreate(
            [
                'teacher_id' => $teacher->id,
                'subject_id' => $paiSubject->id,
                'student_class_id' => $class1->id,
                'day' => 1,
            ],
            [
                'time_start' => '07:00:00',
                'time_end' => '08:30:00',
                'room' => 'Ruang Kelas 1A',
                'jpl' => 2,
            ]
        );

        TeachingSchedule::firstOrCreate(
            [
                'teacher_id' => $teacher->id,
                'subject_id' => $paiSubject->id,
                'student_class_id' => $class2->id,
                'day' => 2,
            ],
            [
                'time_start' => '08:30:00',
                'time_end' => '10:00:00',
                'room' => 'Ruang Kelas 1B',
                'jpl' => 2,
            ]
        );

        // 2. Attendance Records for Today
        $today = Carbon::today()->toDateString();
        $statuses = ['Hadir', 'Hadir', 'Hadir', 'Izin', 'Hadir', 'Sakit'];
        foreach ($students as $index => $student) {
            $status = $statuses[$index % count($statuses)];
            Attendance::updateOrCreate(
                [
                    'user_id' => $student->user_id,
                    'date' => $today,
                ],
                [
                    'status' => $status,
                    'time_in' => $status === 'Hadir' ? '06:50:00' : null,
                    'leave_reason' => $status === 'Izin' ? 'Acara keluarga' : ($status === 'Sakit' ? 'Demam flu' : null),
                ]
            );
        }

        // 3. Lessons (Materi Ajar LMS)
        $lesson1 = Lesson::firstOrCreate(
            [
                'teacher_id' => $teacher->id,
                'subject_id' => $paiSubject->id,
                'student_class_id' => $class1->id,
                'title' => 'Bab 1: Kasih Sayang Nabi Muhammad SAW',
            ],
            [
                'content' => 'Nabi Muhammad SAW adalah panutan utama umat Islam. Beliau memiliki sifat yang sangat penyayang kepada sesama manusia, anak-anak, dan bahkan hewan dan alam sekitar.',
                'attachment' => 'materi_bab1_kisah_nabi.pdf',
                'video_url' => 'https://www.youtube.com/watch?v=demo1',
                'order' => 1,
            ]
        );

        $lesson2 = Lesson::firstOrCreate(
            [
                'teacher_id' => $teacher->id,
                'subject_id' => $paiSubject->id,
                'student_class_id' => $class1->id,
                'title' => 'Bab 2: Mengenal Huruf Hijaiyah & Harakat',
            ],
            [
                'content' => 'Huruf Hijaiyah berjumlah 29 huruf. Harakat dasar terdiri atas Fathah (a), Kasrah (i), dan Dhammah (u). Mari kita lafalkan bersama dengan tartil.',
                'attachment' => 'panduan_hijaiyah_sdam.pdf',
                'video_url' => 'https://www.youtube.com/watch?v=demo2',
                'order' => 2,
            ]
        );

        $lesson3 = Lesson::firstOrCreate(
            [
                'teacher_id' => $teacher->id,
                'subject_id' => $paiSubject->id,
                'student_class_id' => $class2->id,
                'title' => 'Bab 3: Bersih itu Sehat (Thaharah & Berwudhu)',
            ],
            [
                'content' => 'Kebersihan adalah sebagian dari iman. Tata cara berwudhu yang benar dimulai dari niat, membasuh telapak tangan, hingga membasuh kedua kaki.',
                'attachment' => 'modul_wudhu_bergambar.pdf',
                'video_url' => null,
                'order' => 3,
            ]
        );

        // 4. Assignments & Submissions
        $assignment1 = Assignment::firstOrCreate(
            [
                'lesson_id' => $lesson1->id,
                'title' => 'Menuliskan Rangkuman Teladan Nabi Muhammad SAW',
            ],
            [
                'description' => 'Tuliskan di buku catatan 3 contoh perilaku kasih sayang Nabi Muhammad SAW yang bisa kamu terapkan di sekolah, lalu kumpulkan foto tugasmu.',
                'due_date' => Carbon::now()->addDays(2),
                'max_score' => 100,
            ]
        );

        $assignment2 = Assignment::firstOrCreate(
            [
                'lesson_id' => $lesson2->id,
                'title' => 'Praktik Pelafalan Huruf Hijaiyah Berharakat',
            ],
            [
                'description' => 'Rekam audio/video pendek melafalkan huruf Alif sampai Ya dengan harakat Fathah, Kasrah, dan Dhammah.',
                'due_date' => Carbon::now()->addDays(5),
                'max_score' => 100,
            ]
        );

        // Seed some submissions
        foreach ($students->take(3) as $i => $stu) {
            $submission = Submission::firstOrCreate(
                [
                    'assignment_id' => $assignment1->id,
                    'student_id' => $stu->id,
                ],
                [
                    'text_answer' => 'Saya menyayangi teman sekelas, berbagi bekal, dan tersenyum ketika bertemu bapak/ibu guru.',
                    'file_path' => 'tugas_teladan_nabi_'.$stu->id.'.jpg',
                    'score' => $i === 0 ? 95 : null,
                    'feedback' => $i === 0 ? 'Masya Allah, tulisan sangat rapi dan contoh perilakunya sangat bagus!' : null,
                    'submitted_at' => Carbon::now()->subHours(rand(1, 10)),
                ]
            );

            if ($submission->score !== null) {
                Grade::updateOrCreate(
                    [
                        'student_id' => $stu->id,
                        'subject_id' => $paiSubject->id,
                        'teacher_id' => $teacher->id,
                        'type' => 'Tugas',
                        'name' => 'T1',
                    ],
                    ['score' => $submission->score]
                );
            }
        }

        // 5. Grades Seed for all students (Tugas 1-4, Kuis 1-2, UTS)
        foreach ($students as $stu) {
            Grade::firstOrCreate(
                ['student_id' => $stu->id, 'subject_id' => $paiSubject->id, 'teacher_id' => $teacher->id, 'type' => 'Tugas', 'name' => 'T1'],
                ['score' => rand(80, 98)]
            );
            Grade::firstOrCreate(
                ['student_id' => $stu->id, 'subject_id' => $paiSubject->id, 'teacher_id' => $teacher->id, 'type' => 'Tugas', 'name' => 'T2'],
                ['score' => rand(78, 95)]
            );
            Grade::firstOrCreate(
                ['student_id' => $stu->id, 'subject_id' => $paiSubject->id, 'teacher_id' => $teacher->id, 'type' => 'Kuis', 'name' => 'K1'],
                ['score' => rand(82, 100)]
            );
            Grade::firstOrCreate(
                ['student_id' => $stu->id, 'subject_id' => $paiSubject->id, 'teacher_id' => $teacher->id, 'type' => 'UTS', 'name' => 'UTS'],
                ['score' => rand(85, 96)]
            );
        }

        // 6. CBT Exams, Questions & Options
        $exam = Exam::firstOrCreate(
            [
                'teacher_id' => $teacher->id,
                'subject_id' => $paiSubject->id,
                'title' => 'Penilaian Tengah Semester (PTS) PAI & Budi Pekerti',
            ],
            [
                'description' => 'Ujian CBT Tengah Semester Ganjil TA 2026/2027 SD Muhammadiyah Al Mujahidin Wonosari. Kerjakan dengan jujur dan teliti.',
                'start_time' => Carbon::now()->addDays(1)->setTime(8, 0),
                'end_time' => Carbon::now()->addDays(1)->setTime(10, 0),
                'duration_minutes' => 60,
                'max_violations' => 3,
                'is_active' => true,
            ]
        );

        $q1 = Question::firstOrCreate(
            [
                'exam_id' => $exam->id,
                'content' => 'Nabi Muhammad SAW lahir pada tanggal 12 Rabiul Awal yang juga dikenal dengan sebutan Tahun ...',
            ],
            ['type' => 'Pilihan Ganda', 'marks' => 20]
        );

        Option::firstOrCreate(['question_id' => $q1->id, 'content' => 'Tahun Gajah'], ['is_correct' => true]);
        Option::firstOrCreate(['question_id' => $q1->id, 'content' => 'Tahun Unta'], ['is_correct' => false]);
        Option::firstOrCreate(['question_id' => $q1->id, 'content' => 'Tahun Kuda'], ['is_correct' => false]);
        Option::firstOrCreate(['question_id' => $q1->id, 'content' => 'Tahun Hijrah'], ['is_correct' => false]);

        $q2 = Question::firstOrCreate(
            [
                'exam_id' => $exam->id,
                'content' => 'Harakat yang melambangkan bunyi vokal "u" dalam bahasa Arab adalah Dhammah.',
            ],
            ['type' => 'Benar Salah', 'marks' => 20]
        );

        Option::firstOrCreate(['question_id' => $q2->id, 'content' => 'Benar'], ['is_correct' => true]);
        Option::firstOrCreate(['question_id' => $q2->id, 'content' => 'Salah'], ['is_correct' => false]);

        $q3 = Question::firstOrCreate(
            [
                'exam_id' => $exam->id,
                'content' => 'Sebutkan 3 rukun wudhu yang paling utama beserta urutannya!',
            ],
            ['type' => 'Esai', 'marks' => 30]
        );

        // 7. Violation Points Master & Records
        $vp1 = ViolationPoint::firstOrCreate(['name' => 'Terlambat Masuk Sekolah'], ['points' => 5, 'category' => 'Ringan']);
        $vp2 = ViolationPoint::firstOrCreate(['name' => 'Seragam Tidak Rapi / Tidak Sesuai Hari'], ['points' => 5, 'category' => 'Ringan']);
        $vp3 = ViolationPoint::firstOrCreate(['name' => 'Tidak Mengerjakan Tugas / PR'], ['points' => 10, 'category' => 'Ringan']);
        $vp4 = ViolationPoint::firstOrCreate(['name' => 'Mengganggu Ketertiban Saat Sholat Berjamaah'], ['points' => 15, 'category' => 'Sedang']);
        $vp5 = ViolationPoint::firstOrCreate(['name' => 'Membawa HP Tanpa Izin Guru'], ['points' => 20, 'category' => 'Sedang']);

        if ($students->count() >= 2) {
            StudentViolation::firstOrCreate(
                [
                    'student_id' => $students[1]->id,
                    'violation_point_id' => $vp1->id,
                    'date' => Carbon::now()->subDays(2)->toDateString(),
                ],
                [
                    'teacher_id' => $teacher->id,
                    'description' => 'Terlambat tiba di sekolah 15 menit saat pembiasaan pagi sholat Dhuha.',
                    'status' => 'Tercatat',
                ]
            );

            CounselingSession::firstOrCreate(
                [
                    'student_id' => $students[0]->id,
                    'teacher_id' => $teacher->id,
                    'date' => Carbon::now()->toDateString(),
                ],
                [
                    'type' => 'Akademik',
                    'problem' => 'Konsultasi hafalan Juz Amma Surat Al-Balad dan persiapan lomba Tahfidz.',
                    'solution' => 'Diberikan jadwal setor hafalan tambahan 10 menit setelah sholat Dzuhur.',
                    'status' => 'Selesai',
                ]
            );
        }

        // 8. Perpustakaan Books
        Book::firstOrCreate(
            ['isbn' => '978-602-1234-01-1'],
            [
                'title' => 'Kisah 25 Nabi dan Rasul untuk Anak Shalih',
                'author' => 'Tim LPPI Muhammadiyah',
                'publisher' => 'Suara Muhammadiyah',
                'category' => 'Agama Islam',
                'cover_photo' => '📘',
                'file_path' => 'ebook_25_nabi.pdf',
                'physical_stock' => 12,
            ]
        );

        Book::firstOrCreate(
            ['isbn' => '978-602-1234-02-2'],
            [
                'title' => 'Panduan Shalat dan Doa Harian SD Muhammadiyah',
                'author' => 'Majelis Dikdasmen Wonosari',
                'publisher' => 'Pustaka Pelajar',
                'category' => 'Ibadah',
                'cover_photo' => '📗',
                'file_path' => 'panduan_shalat_sdam.pdf',
                'physical_stock' => 25,
            ]
        );

        Book::firstOrCreate(
            ['isbn' => '978-602-1234-03-3'],
            [
                'title' => 'Karakter Cilik: Kejujuran & Kedisiplinan KH Ahmad Dahlan',
                'author' => 'Ahmad Najib',
                'publisher' => 'Suara Muhammadiyah',
                'category' => 'Kemuhammadiyahan',
                'cover_photo' => '📙',
                'file_path' => null,
                'physical_stock' => 8,
            ]
        );

        Book::firstOrCreate(
            ['isbn' => '978-602-1234-04-4'],
            [
                'title' => 'Petualangan Sains Cilik: Mengamati Tumbuhan di Wonosari',
                'author' => 'Dr. Ratna Sari, M.Pd',
                'publisher' => 'Erlangga',
                'category' => 'IPAS',
                'cover_photo' => '🔬',
                'file_path' => null,
                'physical_stock' => 15,
            ]
        );

        // 9. Payroll & Components
        $payroll = Payroll::firstOrCreate(
            [
                'teacher_id' => $teacher->id,
                'month' => 'Oktober',
                'year' => '2026',
            ],
            [
                'total_salary' => 5450000,
                'status' => 'Dibayar',
            ]
        );

        PayrollComponent::firstOrCreate(['payroll_id' => $payroll->id, 'name' => 'Gaji Pokok Pendidik'], ['type' => 'Penerimaan', 'amount' => 3500000]);
        PayrollComponent::firstOrCreate(['payroll_id' => $payroll->id, 'name' => 'Tunjangan Fungsional Guru'], ['type' => 'Penerimaan', 'amount' => 850000]);
        PayrollComponent::firstOrCreate(['payroll_id' => $payroll->id, 'name' => 'Insentif Wali Kelas & Pembiasaan Sholat'], ['type' => 'Penerimaan', 'amount' => 600000]);
        PayrollComponent::firstOrCreate(['payroll_id' => $payroll->id, 'name' => 'Uang Makan & Kehadiran (22 Hari)'], ['type' => 'Penerimaan', 'amount' => 660000]);
        PayrollComponent::firstOrCreate(['payroll_id' => $payroll->id, 'name' => 'Iuran BPJS Ketenagakerjaan & Kesehatan'], ['type' => 'Potongan', 'amount' => 110000]);
        PayrollComponent::firstOrCreate(['payroll_id' => $payroll->id, 'name' => 'Simpanan Pokok & Wajib Koperasi Sekolah'], ['type' => 'Potongan', 'amount' => 50000]);

        // 10. WhatsApp Logs
        WhatsappLog::firstOrCreate(
            [
                'phone_number' => '081234567890',
                'message' => 'Pemberitahuan SD Muhammadiyah Al Mujahidin: Ananda tercatat Sakit hari ini pada mata pelajaran PAI.',
            ],
            [
                'recipient_name' => 'Wali Santri',
                'status' => 'Terkirim',
                'response_log' => '{"success":true,"message_id":"WA-DEMO-001"}',
            ]
        );
    }
}
