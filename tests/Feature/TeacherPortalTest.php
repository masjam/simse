<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\Exam;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\Teacher;
use App\Models\User;
use App\Models\ViolationPoint;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeacherPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;

    private Teacher $teacher;

    private StudentClass $class;

    private Subject $subject;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Guru Pengajar', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);

        $this->teacherUser = User::factory()->create([
            'name' => 'Ustadzah Siti',
            'email' => 'siti@sdmujahidin.sch.id',
        ]);
        $this->teacherUser->assignRole('Guru Pengajar');

        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'nip' => '198407152010012015',
        ]);

        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $this->class = StudentClass::create([
            'academic_year_id' => $academicYear->id,
            'name' => '1 Brilliant',
            'level' => '1',
        ]);

        $this->subject = Subject::create([
            'code' => 'PAI',
            'name' => 'Pendidikan Agama Islam dan Budi Pekerti',
        ]);

        $studentUser = User::factory()->create(['name' => 'Ahmad Faiz']);
        $studentUser->assignRole('Siswa');
        $this->student = Student::create([
            'user_id' => $studentUser->id,
            'student_class_id' => $this->class->id,
            'nisn' => '0012345678',
            'nik' => '3201010101010001',
        ]);
    }

    public function test_guest_cannot_access_teacher_portal(): void
    {
        $response = $this->get(route('guru.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_teacher_can_access_dashboard_and_pages(): void
    {
        $this->actingAs($this->teacherUser);

        $this->get(route('guru.dashboard'))->assertOk();
        $this->get(route('guru.jadwal'))->assertOk();
        $this->get(route('guru.absensi'))->assertOk();
        $this->get(route('guru.nilai'))->assertOk();
        $this->get(route('guru.raport'))->assertOk();
        $this->get(route('guru.lms'))->assertOk();
        $this->get(route('guru.tugas'))->assertOk();
        $this->get(route('guru.cbt'))->assertOk();
        $this->get(route('guru.bk'))->assertOk();
        $this->get(route('guru.perpustakaan'))->assertOk();
        $this->get(route('guru.payroll'))->assertOk();
    }

    public function test_teacher_can_save_attendance(): void
    {
        $this->actingAs($this->teacherUser);

        $today = Carbon::today()->toDateString();
        $response = $this->post(route('guru.absensi.store'), [
            'class_name' => $this->class->name,
            'date' => $today,
            'students' => [
                [
                    'id' => $this->student->id,
                    'status' => 'Izin',
                    'leave_reason' => 'Ada acara keluarga',
                ],
            ],
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->student->user_id,
            'date' => $today,
            'status' => 'Izin',
            'leave_reason' => 'Ada acara keluarga',
        ]);
    }

    public function test_teacher_can_store_grades(): void
    {
        $this->actingAs($this->teacherUser);

        $response = $this->post(route('guru.nilai.store'), [
            'class_name' => $this->class->name,
            'subject_name' => $this->subject->name,
            'type' => 'tugas',
            'students' => [
                [
                    'id' => $this->student->id,
                    'tugas' => [90, 95],
                ],
            ],
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('grades', [
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'name' => 'T1',
            'score' => 90,
        ]);
        $this->assertDatabaseHas('grades', [
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'name' => 'T2',
            'score' => 95,
        ]);
    }

    public function test_teacher_can_create_lesson_and_assignment(): void
    {
        $this->actingAs($this->teacherUser);

        $response = $this->post(route('guru.lms.store'), [
            'title' => 'Bab 1: Mengenal Rukun Iman',
            'subject_name' => $this->subject->name,
            'class_name' => $this->class->name,
            'content' => 'Rukun iman ada 6 perkara.',
            'order' => 1,
        ]);

        $response->assertSessionHas('success');
        $lesson = Lesson::where('title', 'Bab 1: Mengenal Rukun Iman')->first();
        $this->assertNotNull($lesson);

        // Create assignment
        $assignResponse = $this->post(route('guru.tugas.store'), [
            'title' => 'Menghafal 6 Rukun Iman',
            'lesson_id' => $lesson->id,
            'description' => 'Hafalkan rukun iman secara urut.',
            'due_date' => Carbon::now()->addDays(3)->toDateTimeString(),
            'max_score' => 100,
        ]);

        $assignResponse->assertSessionHas('success');
        $this->assertDatabaseHas('assignments', [
            'lesson_id' => $lesson->id,
            'title' => 'Menghafal 6 Rukun Iman',
        ]);
    }

    public function test_teacher_can_grade_student_submission(): void
    {
        $this->actingAs($this->teacherUser);

        $lesson = Lesson::create([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'student_class_id' => $this->class->id,
            'title' => 'Bab Demo',
        ]);

        $assignment = Assignment::create([
            'lesson_id' => $lesson->id,
            'title' => 'Tugas Menulis',
            'due_date' => Carbon::now()->addDays(2),
            'max_score' => 100,
        ]);

        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'text_answer' => 'Jawaban santri.',
            'submitted_at' => Carbon::now(),
        ]);

        $response = $this->post(route('guru.tugas.grade', $submission->id), [
            'score' => 98,
            'feedback' => 'Masya Allah, sangat baik!',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'score' => 98,
            'feedback' => 'Masya Allah, sangat baik!',
        ]);
        $this->assertDatabaseHas('grades', [
            'student_id' => $this->student->id,
            'type' => 'Tugas',
            'score' => 98,
        ]);
    }

    public function test_teacher_can_schedule_cbt_exam_and_add_question(): void
    {
        $this->actingAs($this->teacherUser);

        $response = $this->post(route('guru.cbt.exam.store'), [
            'title' => 'PTS PAI Semester Ganjil',
            'subject_name' => $this->subject->name,
            'start_time' => Carbon::now()->addDay()->toDateTimeString(),
            'end_time' => Carbon::now()->addDay()->addHours(2)->toDateTimeString(),
            'duration_minutes' => 60,
            'max_violations' => 3,
            'description' => 'Kerjakan dengan teliti.',
        ]);

        $response->assertSessionHas('success');
        $exam = Exam::where('title', 'PTS PAI Semester Ganjil')->first();
        $this->assertNotNull($exam);

        // Add question
        $qResponse = $this->post(route('guru.cbt.question.store', $exam->id), [
            'type' => 'Pilihan Ganda',
            'content' => 'Nabi pertama umat manusia adalah ...',
            'marks' => 20,
            'options' => [
                ['content' => 'Nabi Adam AS', 'is_correct' => true],
                ['content' => 'Nabi Nuh AS', 'is_correct' => false],
            ],
        ]);

        $qResponse->assertSessionHas('success');
        $this->assertDatabaseHas('questions', [
            'exam_id' => $exam->id,
            'content' => 'Nabi pertama umat manusia adalah ...',
        ]);
        $this->assertDatabaseHas('options', [
            'content' => 'Nabi Adam AS',
            'is_correct' => true,
        ]);
    }

    public function test_teacher_can_record_violation_and_counseling(): void
    {
        $this->actingAs($this->teacherUser);

        $vp = ViolationPoint::create([
            'name' => 'Terlambat Datang',
            'points' => 5,
            'category' => 'Ringan',
        ]);

        $response = $this->post(route('guru.bk.violation.store'), [
            'student_id' => $this->student->id,
            'violation_point_id' => $vp->id,
            'date' => Carbon::today()->toDateString(),
            'description' => 'Terlambat 10 menit saat sholat Dhuha.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_violations', [
            'student_id' => $this->student->id,
            'violation_point_id' => $vp->id,
            'status' => 'Tercatat',
        ]);

        // Record counseling
        $cResponse = $this->post(route('guru.bk.counseling.store'), [
            'student_id' => $this->student->id,
            'type' => 'Akademik',
            'date' => Carbon::today()->toDateString(),
            'problem' => 'Motivasi belajar hafalan.',
            'solution' => 'Diberikan pendampingan hafalan.',
            'status' => 'Selesai',
        ]);

        $cResponse->assertSessionHas('success');
        $this->assertDatabaseHas('counseling_sessions', [
            'student_id' => $this->student->id,
            'type' => 'Akademik',
            'status' => 'Selesai',
        ]);
    }
}
