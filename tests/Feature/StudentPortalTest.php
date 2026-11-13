<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Bill;
use App\Models\BillType;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Grade;
use App\Models\Lesson;
use App\Models\Option;
use App\Models\Question;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentViolation;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\Teacher;
use App\Models\TeachingSchedule;
use App\Models\Tingkat;
use App\Models\User;
use App\Models\ViolationPoint;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StudentPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $studentUser;

    private Student $student;

    private User $otherStudentUser;

    private Student $otherStudent;

    private StudentClass $classA;

    private StudentClass $classB;

    private Teacher $teacher;

    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Guru Pengajar', 'guard_name' => 'web']);

        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $tingkat4 = Tingkat::create(['name' => 'Kelas 4']);
        $tingkat5 = Tingkat::create(['name' => 'Kelas 5']);

        $teacherUser = User::factory()->create(['name' => 'Ibu Siti Guru']);
        $teacherUser->assignRole('Guru Pengajar');
        $this->teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'nip' => '198407152010012015',
        ]);

        $this->classA = StudentClass::create([
            'academic_year_id' => $academicYear->id,
            'tingkat_id' => $tingkat4->id,
            'teacher_id' => $this->teacher->id,
            'level' => '4',
            'name' => '4A',
        ]);

        $this->classB = StudentClass::create([
            'academic_year_id' => $academicYear->id,
            'tingkat_id' => $tingkat5->id,
            'teacher_id' => $this->teacher->id,
            'level' => '5',
            'name' => '5A',
        ]);

        $this->subject = Subject::create([
            'code' => 'PAI',
            'name' => 'Pendidikan Agama Islam',
        ]);

        $this->studentUser = User::factory()->create(['name' => 'Andi Siswa']);
        $this->studentUser->assignRole('Siswa');
        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'student_class_id' => $this->classA->id,
            'nisn' => '0150000001',
            'nik' => '3403010101160001',
            'gender' => 'L',
            'qr_code' => 'QR-ANDI-001',
        ]);

        $this->otherStudentUser = User::factory()->create(['name' => 'Budi Kelas Lain']);
        $this->otherStudentUser->assignRole('Siswa');
        $this->otherStudent = Student::create([
            'user_id' => $this->otherStudentUser->id,
            'student_class_id' => $this->classB->id,
            'nisn' => '0150000002',
            'nik' => '3403010101160002',
            'gender' => 'L',
            'qr_code' => 'QR-BUDI-002',
        ]);
    }

    public function test_non_student_cannot_access_student_portal(): void
    {
        $guru = User::factory()->create();
        $guru->assignRole('Guru Pengajar');

        $this->actingAs($guru)
            ->get(route('siswa.dashboard'))
            ->assertForbidden();
    }

    public function test_student_can_view_dashboard_jadwal_nilai_presensi_and_kts_with_own_data_only(): void
    {
        TeachingSchedule::create([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'student_class_id' => $this->classA->id,
            'day' => Carbon::now()->dayOfWeekIso,
            'time_start' => '07:30:00',
            'time_end' => '09:00:00',
            'room' => 'Ruang 4A',
            'jpl' => 2,
        ]);

        TeachingSchedule::create([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'student_class_id' => $this->classB->id,
            'day' => Carbon::now()->dayOfWeekIso,
            'time_start' => '10:00:00',
            'time_end' => '11:30:00',
            'room' => 'Ruang 5A',
            'jpl' => 2,
        ]);

        Grade::create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'type' => 'Tugas',
            'name' => 'Tugas 1',
            'score' => 92,
        ]);

        Grade::create([
            'student_id' => $this->otherStudent->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'type' => 'Tugas',
            'name' => 'Rahasia Budi',
            'score' => 60,
        ]);

        Attendance::create([
            'user_id' => $this->studentUser->id,
            'date' => Carbon::today()->toDateString(),
            'status' => 'Hadir',
            'time_in' => '06:50:00',
        ]);

        $billType = BillType::create(['name' => 'SPP Bulanan', 'amount' => 300000, 'period' => 'Bulanan']);
        Bill::create([
            'student_id' => $this->student->id,
            'bill_type_id' => $billType->id,
            'month' => 'November',
            'year' => '2026',
            'amount_due' => 300000,
            'status' => 'Belum Lunas',
        ]);

        $vp = ViolationPoint::create(['name' => 'Terlambat', 'points' => 5, 'category' => 'Ringan']);
        StudentViolation::create([
            'student_id' => $this->student->id,
            'violation_point_id' => $vp->id,
            'teacher_id' => $this->teacher->id,
            'date' => Carbon::today()->toDateString(),
            'status' => 'Tercatat',
        ]);

        $this->actingAs($this->studentUser)
            ->get(route('siswa.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Student/Dashboard')
                ->where('student.nisn', '0150000001')
                ->where('summary.violation_points', 5)
                ->missing('summary.unpaid_bills_total')
                ->missing('recentBills')
                ->has('todaySchedules', 1)
                ->where('todaySchedules.0.room', 'Ruang 4A')
            );

        $this->actingAs($this->studentUser)
            ->get(route('siswa.nilai'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Student/Nilai')
                ->has('grades', 1)
                ->where('grades.0.name', 'Tugas 1')
                ->where('grades.0.score', 92)
            );

        $this->actingAs($this->studentUser)
            ->get(route('siswa.presensi'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Student/Presensi')
                ->where('summary.hadir', 1)
            );

        $this->actingAs($this->studentUser)
            ->get(route('kts'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Student/KTS')
                ->where('student.name', 'Andi Siswa')
                ->where('student.qr_value', 'QR-ANDI-001')
                ->where('student.class_name', '4A')
            );
    }

    public function test_student_can_access_own_class_lms_and_submit_assignment_but_is_blocked_from_other_class(): void
    {
        Storage::fake('local');

        Storage::disk('local')->put('materials/kelas4a.pdf', 'konten-materi-4a');
        Storage::disk('local')->put('materials/kelas5a.pdf', 'konten-materi-5a');

        $lesson4A = Lesson::create([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'student_class_id' => $this->classA->id,
            'title' => 'Materi Kelas 4A',
            'content' => 'Ringkasan 4A',
            'attachment' => 'materials/kelas4a.pdf',
            'order' => 1,
        ]);

        $lesson5A = Lesson::create([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'student_class_id' => $this->classB->id,
            'title' => 'Materi Kelas 5A',
            'content' => 'Ringkasan 5A',
            'attachment' => 'materials/kelas5a.pdf',
            'order' => 1,
        ]);

        $assignment4A = Assignment::create([
            'lesson_id' => $lesson4A->id,
            'title' => 'Tugas Kelas 4A',
            'due_date' => Carbon::now()->addDays(2),
            'max_score' => 100,
        ]);

        $assignment5A = Assignment::create([
            'lesson_id' => $lesson5A->id,
            'title' => 'Tugas Kelas 5A',
            'due_date' => Carbon::now()->addDays(2),
            'max_score' => 100,
        ]);

        // Siswa 4A melihat materi 4A saja
        $this->actingAs($this->studentUser)
            ->get(route('siswa.lms'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Student/LMS')
                ->has('lessons', 1)
                ->where('lessons.0.title', 'Materi Kelas 4A')
            );

        // Unduh lampiran kelas sendiri berhasil, kelas lain ditolak 403
        $this->actingAs($this->studentUser)
            ->get(route('siswa.lms.attachment', $lesson4A->id))
            ->assertOk();

        $this->actingAs($this->studentUser)
            ->get(route('siswa.lms.attachment', $lesson5A->id))
            ->assertForbidden();

        // Kumpulkan tugas kelas sendiri berhasil dan simpan ke disk local
        $file = UploadedFile::fake()->create('jawaban_andi.pdf', 120, 'application/pdf');
        $this->actingAs($this->studentUser)
            ->post(route('siswa.tugas.submit', $assignment4A->id), [
                'text_answer' => 'Ini jawaban tugas saya.',
                'file' => $file,
            ])
            ->assertRedirect();

        $submission = Submission::where('assignment_id', $assignment4A->id)
            ->where('student_id', $this->student->id)
            ->first();

        $this->assertNotNull($submission);
        $this->assertSame('Ini jawaban tugas saya.', $submission->text_answer);
        Storage::disk('local')->assertExists($submission->file_path);

        // Siswa lain tidak bisa mengunduh submission milik Andi
        $this->actingAs($this->otherStudentUser)
            ->get(route('siswa.submissions.download', $submission->id))
            ->assertForbidden();

        // Siswa 4A tidak bisa mengumpulkan tugas ke kelas 5A
        $this->actingAs($this->studentUser)
            ->post(route('siswa.tugas.submit', $assignment5A->id), [
                'text_answer' => 'Mencoba akses kelas lain.',
            ])
            ->assertForbidden();
    }

    public function test_student_can_take_cbt_preserve_server_timer_and_autograde_objective_questions(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 12, 8, 0, 0));

        $exam = Exam::create([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'title' => 'Ujian Harian PAI',
            'start_time' => Carbon::now()->subMinutes(10),
            'end_time' => Carbon::now()->addHours(2),
            'duration_minutes' => 30,
            'max_violations' => 3,
            'is_active' => true,
        ]);

        $qPg = Question::create([
            'exam_id' => $exam->id,
            'type' => 'Pilihan Ganda',
            'content' => 'Siapa rasul terakhir?',
            'marks' => 50,
        ]);
        $pgCorrect = Option::create(['question_id' => $qPg->id, 'content' => 'Nabi Muhammad SAW', 'is_correct' => true]);
        Option::create(['question_id' => $qPg->id, 'content' => 'Nabi Nuh AS', 'is_correct' => false]);

        $qBs = Question::create([
            'exam_id' => $exam->id,
            'type' => 'Benar Salah',
            'content' => 'Sholat subuh berjumlah 2 rakaat.',
            'marks' => 50,
        ]);
        $bsCorrect = Option::create(['question_id' => $qBs->id, 'content' => 'Benar', 'is_correct' => true]);
        Option::create(['question_id' => $qBs->id, 'content' => 'Salah', 'is_correct' => false]);

        // Mulai ujian
        $this->actingAs($this->studentUser)
            ->get(route('siswa.cbt.start', $exam->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Student/CBTExam')
                ->where('remainingSeconds', 1800)
                ->missing('questions.0.options.0.is_correct')
            );

        // Simulasi waktu berjalan 5 menit lalu refresh -> timer tetap berkurang (1500 detik)
        Carbon::setTestNow(Carbon::now()->addMinutes(5));

        $this->actingAs($this->studentUser)
            ->get(route('siswa.cbt.start', $exam->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('remainingSeconds', 1500)
            );

        // Simpan jawaban via endpoint auto-save
        $this->actingAs($this->studentUser)
            ->postJson(route('siswa.cbt.answer', $exam->id), [
                'question_id' => $qPg->id,
                'option_id' => $pgCorrect->id,
            ])
            ->assertOk()
            ->assertJson(['saved' => true]);

        // Submit ujian dengan jawaban Benar/Salah ikut dikirim saat final submit
        $this->actingAs($this->studentUser)
            ->post(route('siswa.cbt.submit', $exam->id), [
                'answers' => [
                    $qBs->id => ['option_id' => $bsCorrect->id],
                ],
            ])
            ->assertRedirect(route('siswa.cbt.start', $exam->id));

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $this->student->id)
            ->first();

        $this->assertNotNull($attempt?->finished_at);
        $this->assertFalse($attempt->is_forced_submit);
        $this->assertEquals(100.0, $attempt->score);

        Carbon::setTestNow();
    }

    public function test_cbt_anti_cheat_records_violations_and_forces_submit_when_limit_reached(): void
    {
        $exam = Exam::create([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'title' => 'Ujian Anti Cheat',
            'start_time' => Carbon::now()->subMinutes(5),
            'end_time' => Carbon::now()->addHours(1),
            'duration_minutes' => 40,
            'max_violations' => 2,
            'is_active' => true,
        ]);

        $q = Question::create([
            'exam_id' => $exam->id,
            'type' => 'Pilihan Ganda',
            'content' => 'Rukun Islam ada berapa?',
            'marks' => 100,
        ]);
        $correct = Option::create(['question_id' => $q->id, 'content' => '5', 'is_correct' => true]);

        $this->actingAs($this->studentUser)
            ->get(route('siswa.cbt.start', $exam->id))
            ->assertOk();

        $this->actingAs($this->studentUser)
            ->postJson(route('siswa.cbt.answer', $exam->id), [
                'question_id' => $q->id,
                'option_id' => $correct->id,
            ])
            ->assertOk();

        // Pelanggaran 1: belum paksa submit
        $this->actingAs($this->studentUser)
            ->postJson(route('siswa.cbt.violation', $exam->id), [
                'type' => 'Tab Switch',
            ])
            ->assertOk()
            ->assertJson([
                'violation_count' => 1,
                'max_violations' => 2,
                'forced_submit' => false,
            ]);

        // Pelanggaran 2: mencapai max_violations -> auto forced submit!
        $this->actingAs($this->studentUser)
            ->postJson(route('siswa.cbt.violation', $exam->id), [
                'type' => 'Keluar Layar Penuh',
            ])
            ->assertOk()
            ->assertJson([
                'violation_count' => 2,
                'max_violations' => 2,
                'forced_submit' => true,
            ]);

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $this->student->id)
            ->first();

        $this->assertTrue($attempt->is_forced_submit);
        $this->assertNotNull($attempt->finished_at);
        $this->assertEquals(100.0, $attempt->score);
        $this->assertSame(2, $attempt->violations()->count());
    }
}
