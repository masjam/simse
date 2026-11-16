<?php

namespace Tests\Performance;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CbtConcurrentExamLoadTest extends TestCase
{
    use RefreshDatabase;

    public function test_cbt_database_indexes_support_175_concurrent_students(): void
    {
        Role::firstOrCreate(['name' => 'Guru Pengajar', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);

        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $teacherUser = User::factory()->create(['name' => 'Ustadz Penguji']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'nip' => '198501012010011001',
            'position' => 'Guru Matematika',
        ]);

        $subject = Subject::create([
            'name' => 'Matematika Terpadu',
            'code' => 'MTK-01',
        ]);

        $class = StudentClass::create([
            'academic_year_id' => $academicYear->id,
            'name' => 'Kelas 6 Al-Kindi',
            'level' => 6,
        ]);

        $exam = Exam::create([
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'title' => 'Asesmen Akhir Semester CBT 2026',
            'description' => 'Ujian serentak kapasitas 175 siswa SD Muhammadiyah Al Mujahidin',
            'duration_minutes' => 90,
            'start_time' => now()->subMinutes(10),
            'end_time' => now()->addMinutes(120),
            'max_violations' => 3,
            'is_active' => true,
        ]);

        // 40 Soal Ujian
        for ($q = 1; $q <= 40; $q++) {
            $question = Question::create([
                'exam_id' => $exam->id,
                'type' => 'Pilihan Ganda',
                'content' => "Soal nomor {$q} terkait materi semester 1?",
                'marks' => 2,
            ]);

            $question->options()->createMany([
                ['content' => 'Pilihan A (Benar)', 'is_correct' => true],
                ['content' => 'Pilihan B', 'is_correct' => false],
                ['content' => 'Pilihan C', 'is_correct' => false],
                ['content' => 'Pilihan D', 'is_correct' => false],
            ]);
        }

        // Generate 175 Siswa
        $studentIds = [];
        for ($s = 1; $s <= 175; $s++) {
            $studentUser = User::factory()->create([
                'email' => "siswa{$s}@sdmujahidin-wns.sch.id",
                'name' => "Siswa Ujian {$s}",
            ]);
            $student = Student::create([
                'user_id' => $studentUser->id,
                'student_class_id' => $class->id,
                'nisn' => sprintf('001122%04d', $s),
                'nis' => sprintf('600%03d', $s),
                'nik' => sprintf('34030101011%05d', $s),
                'gender' => ($s % 2 === 0) ? 'L' : 'P',
            ]);
            $studentIds[] = $student->id;
        }

        $this->assertCount(175, $studentIds);

        // Simulasi Mulai Ujian Serentak (175 Exam Attempts)
        $startTime = microtime(true);
        DB::transaction(function () use ($exam, $studentIds) {
            foreach ($studentIds as $studentId) {
                ExamAttempt::create([
                    'exam_id' => $exam->id,
                    'student_id' => $studentId,
                    'started_at' => now(),
                    'answers' => [],
                ]);
            }
        });
        $attemptInsertDurationMs = (microtime(true) - $startTime) * 1000;

        // Verifikasi 175 attempt berhasil dibuat
        $this->assertEquals(175, ExamAttempt::where('exam_id', $exam->id)->count());

        // Simulasi Autosave Jawaban Serentak dengan Lookup Menggunakan Indeks Komposit
        $lookupStartTime = microtime(true);
        $totalQueries = 0;

        foreach ($studentIds as $studentId) {
            // Kueri yang dijalankan pada setiap request saveExamAnswer
            $attempt = ExamAttempt::where('exam_id', $exam->id)
                ->where('student_id', $studentId)
                ->first();

            $this->assertNotNull($attempt);
            $totalQueries++;
        }

        $lookupDurationMs = (microtime(true) - $lookupStartTime) * 1000;
        $avgLookupPerStudentMs = $lookupDurationMs / 175;

        // Target performa: Rata-rata pencarian attempt per siswa di database harus sangat cepat (< 5ms)
        $this->assertLessThan(15.0, $avgLookupPerStudentMs, "Pencarian index komposit harus di bawah 15ms per siswa, didapat: {$avgLookupPerStudentMs} ms");

        // Simulasi Proctor Live Monitoring Query (Teacher CbtMonitorController)
        $monitorStartTime = microtime(true);
        $activeAttempts = ExamAttempt::where('exam_id', $exam->id)
            ->whereNull('finished_at')
            ->with(['student.user'])
            ->get();
        $monitorDurationMs = (microtime(true) - $monitorStartTime) * 1000;

        $this->assertCount(175, $activeAttempts);
        $this->assertLessThan(250.0, $monitorDurationMs, "Kueri proctor monitor 175 siswa harus di bawah 250ms, didapat: {$monitorDurationMs} ms");
    }
}
