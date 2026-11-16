<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamStudentTarget;
use App\Models\ExamViolation;
use App\Models\Option;
use App\Models\Question;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CbtAdvancedWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;

    private Teacher $teacher;

    private User $studentUser1;

    private Student $student1;

    private User $studentUser2;

    private Student $student2;

    private Subject $subject;

    private AcademicYear $year;

    private StudentClass $class;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Guru Pengajar', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);

        $this->year = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $this->teacherUser = User::factory()->create(['name' => 'Ustadz Salman, S.Pd.I.']);
        $this->teacherUser->assignRole('Guru Pengajar');

        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'nip' => '198801012015011001',
        ]);

        $this->class = StudentClass::create([
            'name' => '5 Abu Bakar',
            'level' => '5',
            'teacher_id' => $this->teacher->id,
            'academic_year_id' => $this->year->id,
        ]);

        $this->studentUser1 = User::factory()->create(['name' => 'Ahmad Dahlan']);
        $this->studentUser1->assignRole('Siswa');
        $this->student1 = Student::create([
            'user_id' => $this->studentUser1->id,
            'student_class_id' => $this->class->id,
            'nis' => '202605001',
            'nisn' => '0015001',
            'nik' => '3403010101180011',
            'no_telp_ortu' => '081234567801',
            'gender' => 'L',
        ]);

        $this->studentUser2 = User::factory()->create(['name' => 'Siti Walidah']);
        $this->studentUser2->assignRole('Siswa');
        $this->student2 = Student::create([
            'user_id' => $this->studentUser2->id,
            'student_class_id' => $this->class->id,
            'nis' => '202605002',
            'nisn' => '0015002',
            'nik' => '3403010101180012',
            'no_telp_ortu' => '081234567802',
            'gender' => 'P',
        ]);

        $this->subject = Subject::create([
            'name' => 'Pendidikan Al-Islam',
            'code' => 'PAI',
        ]);
    }

    public function test_teacher_can_create_exam_with_custom_scoring_rule_and_randomization(): void
    {
        $response = $this->actingAs($this->teacherUser)->post(route('guru.cbt.exam.store'), [
            'title' => 'Asesmen Formatif PAI Bab 1',
            'subject_name' => $this->subject->name,
            'start_time' => now()->subHour()->toDateTimeString(),
            'end_time' => now()->addHours(2)->toDateTimeString(),
            'duration_minutes' => 60,
            'max_violations' => 3,
            'scoring_rule' => 'partial',
            'randomize_questions' => true,
            'randomize_options' => true,
            'is_remedial' => false,
            'description' => 'Kerjakan dengan teliti dan jujur.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('exams', [
            'title' => 'Asesmen Formatif PAI Bab 1',
            'scoring_rule' => 'partial',
            'randomize_questions' => true,
        ]);
    }

    public function test_teacher_can_create_all_six_question_types(): void
    {
        $exam = Exam::create([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'title' => 'Ujian 6 Tipe Soal',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
            'duration_minutes' => 60,
            'max_violations' => 3,
            'scoring_rule' => 'partial',
            'is_active' => true,
        ]);

        // 1. Pilihan Ganda
        $this->actingAs($this->teacherUser)->post(route('guru.cbt.question.store', $exam->id), [
            'type' => 'Pilihan Ganda',
            'content' => 'Siapa pendiri Muhammadiyah?',
            'marks' => 15,
            'options' => [
                ['content' => 'KH Ahmad Dahlan', 'is_correct' => true],
                ['content' => 'KH Hasyim Asyari', 'is_correct' => false],
            ],
        ])->assertRedirect();

        // 2. Ganda Kompleks
        $this->actingAs($this->teacherUser)->post(route('guru.cbt.question.store', $exam->id), [
            'type' => 'Ganda Kompleks',
            'content' => 'Pilihlah rukun iman di bawah ini:',
            'marks' => 20,
            'options' => [
                ['content' => 'Iman kepada Allah', 'is_correct' => true],
                ['content' => 'Iman kepada Malaikat', 'is_correct' => true],
                ['content' => 'Membayar Zakat', 'is_correct' => false],
            ],
        ])->assertRedirect();

        // 3. Benar Salah
        $this->actingAs($this->teacherUser)->post(route('guru.cbt.question.store', $exam->id), [
            'type' => 'Benar Salah',
            'content' => 'SD Muhammadiyah Al Mujahidin berada di Wonosari.',
            'marks' => 10,
            'options' => [
                ['content' => 'Benar', 'is_correct' => true],
                ['content' => 'Salah', 'is_correct' => false],
            ],
        ])->assertRedirect();

        // 4. Menjodohkan
        $this->actingAs($this->teacherUser)->post(route('guru.cbt.question.store', $exam->id), [
            'type' => 'Menjodohkan',
            'content' => 'Jodohkan nabi dengan mukzijatnya:',
            'marks' => 20,
            'options' => [
                ['content' => 'Nabi Musa AS', 'pair_match' => 'Membelah Laut Merah', 'is_correct' => true],
                ['content' => 'Nabi Nuh AS', 'pair_match' => 'Bahtera Besar', 'is_correct' => true],
            ],
        ])->assertRedirect();

        // 5. Isian Rumpang
        $this->actingAs($this->teacherUser)->post(route('guru.cbt.question.store', $exam->id), [
            'type' => 'Isian Rumpang',
            'content' => 'Ibu kota negara Indonesia yang baru adalah [...]',
            'marks' => 15,
            'metadata' => [
                'blanks' => [
                    0 => ['accepted' => ['Nusantara', 'IKN'], 'ignore_punct' => true],
                ],
            ],
        ])->assertRedirect();

        // 6. Esai
        $this->actingAs($this->teacherUser)->post(route('guru.cbt.question.store', $exam->id), [
            'type' => 'Esai',
            'content' => 'Jelaskan hikmah berbakti kepada orang tua.',
            'marks' => 20,
        ])->assertRedirect();

        $this->assertEquals(6, $exam->questions()->count());
    }

    public function test_student_cannot_see_answer_keys_in_cbt_payload(): void
    {
        $exam = Exam::create([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'title' => 'Ujian Rahasia Kunci',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
            'duration_minutes' => 60,
            'max_violations' => 3,
            'is_active' => true,
        ]);

        $q = Question::create([
            'exam_id' => $exam->id,
            'type' => 'Pilihan Ganda',
            'content' => 'Soal 1',
            'marks' => 10,
        ]);

        Option::create(['question_id' => $q->id, 'content' => 'Kunci Benar', 'is_correct' => true]);
        Option::create(['question_id' => $q->id, 'content' => 'Opsi Salah', 'is_correct' => false]);

        $response = $this->actingAs($this->studentUser1)->get(route('siswa.cbt.start', $exam->id));

        $response->assertStatus(200);

        // Assert inertia page props do NOT expose is_correct or secret keys
        $pageProps = $response->viewData('page')['props'];
        $renderedQuestions = $pageProps['questions'];

        $this->assertNotEmpty($renderedQuestions);
        foreach ($renderedQuestions[0]['options'] as $opt) {
            $this->assertArrayNotHasKey('is_correct', $opt, 'Security: is_correct must never leak to student payload!');
            $this->assertArrayNotHasKey('pair_match', $opt, 'Security: pair_match must never leak to student options!');
        }
    }

    public function test_remedial_exam_is_only_accessible_to_targeted_students(): void
    {
        $exam = Exam::create([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'title' => 'Ujian Remedial PAI',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
            'duration_minutes' => 60,
            'max_violations' => 3,
            'is_remedial' => true,
            'is_active' => true,
        ]);

        Question::create([
            'exam_id' => $exam->id,
            'type' => 'Pilihan Ganda',
            'content' => 'Soal Remedial',
            'marks' => 10,
        ]);

        // Target only student1
        ExamStudentTarget::create([
            'exam_id' => $exam->id,
            'student_id' => $this->student1->id,
        ]);

        // Student 1 (targeted) can enter
        $this->actingAs($this->studentUser1)
            ->get(route('siswa.cbt.start', $exam->id))
            ->assertStatus(200);

        // Student 2 (NOT targeted) is redirected with error
        $this->actingAs($this->studentUser2)
            ->get(route('siswa.cbt.start', $exam->id))
            ->assertRedirect(route('siswa.cbt'))
            ->assertSessionHas('error');
    }

    public function test_full_cbt_workflow_autosave_submit_and_automatic_evaluation(): void
    {
        $exam = Exam::create([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'title' => 'CBT Full Evaluation',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
            'duration_minutes' => 60,
            'max_violations' => 3,
            'scoring_rule' => 'standard',
            'is_active' => true,
        ]);

        // Question 1: PG (50 marks)
        $q1 = Question::create(['exam_id' => $exam->id, 'type' => 'Pilihan Ganda', 'content' => 'Q1', 'marks' => 50]);
        $optCorrect = Option::create(['question_id' => $q1->id, 'content' => 'Correct', 'is_correct' => true]);
        $optWrong = Option::create(['question_id' => $q1->id, 'content' => 'Wrong', 'is_correct' => false]);

        // Question 2: Isian Rumpang (50 marks)
        $q2 = Question::create([
            'exam_id' => $exam->id,
            'type' => 'Isian Rumpang',
            'content' => 'Q2 [...]',
            'marks' => 50,
            'metadata' => ['blanks' => [0 => ['accepted' => ['Al-Mujahidin'], 'ignore_punct' => true]]],
        ]);

        // Start exam
        $this->actingAs($this->studentUser1)->get(route('siswa.cbt.start', $exam->id));
        $attempt = ExamAttempt::where('exam_id', $exam->id)->where('student_id', $this->student1->id)->first();
        $this->assertNotNull($attempt);

        // Autosave answer Q1
        $resSave = $this->actingAs($this->studentUser1)->postJson(route('siswa.cbt.answer', $exam->id), [
            'question_id' => $q1->id,
            'option_id' => $optCorrect->id,
        ]);
        $resSave->assertStatus(200)->assertJson(['saved' => true]);

        // Submit final exam with both answers
        $this->actingAs($this->studentUser1)->post(route('siswa.cbt.submit', $exam->id), [
            'answers' => [
                $q1->id => ['option_id' => $optCorrect->id],
                $q2->id => ['blanks' => ['  al-mujahidin! ']], // test normalization
            ],
        ])->assertRedirect();

        $attempt->refresh();
        $this->assertNotNull($attempt->finished_at);
        $this->assertEquals(100.0, $attempt->score); // 50 + 50 = 100%

        // Grade synchronization check
        $this->assertDatabaseHas('grades', [
            'student_id' => $this->student1->id,
            'subject_id' => $this->subject->id,
            'type' => 'Kuis',
            'score' => 100.0,
        ]);
    }

    public function test_teacher_can_monitor_poll_add_extra_time_and_reset_violations(): void
    {
        $exam = Exam::create([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'title' => 'CBT Live Monitor Test',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
            'duration_minutes' => 60,
            'max_violations' => 3,
            'is_active' => true,
        ]);

        Question::create(['exam_id' => $exam->id, 'type' => 'Pilihan Ganda', 'content' => 'Q', 'marks' => 10]);

        // Student starts exam
        $this->actingAs($this->studentUser1)->get(route('siswa.cbt.start', $exam->id));
        $attempt = ExamAttempt::where('exam_id', $exam->id)->where('student_id', $this->student1->id)->first();

        // Record a violation
        ExamViolation::create([
            'exam_attempt_id' => $attempt->id,
            'type' => 'Tab Switch',
            'timestamp' => now(),
        ]);

        // Teacher polls monitor
        $resPoll = $this->actingAs($this->teacherUser)->getJson(route('guru.cbt.monitor.poll', $exam->id));
        $resPoll->assertStatus(200);
        $this->assertEquals(1, $resPoll->json('counts.violations_total'));

        // Teacher adds extra time (+15 mins)
        $this->actingAs($this->teacherUser)->postJson(route('guru.cbt.monitor.extra-time', $attempt->id), [
            'minutes' => 15,
        ])->assertStatus(200)->assertJson(['success' => true]);

        $attempt->refresh();
        $this->assertEquals(15, $attempt->extra_time_minutes);

        // Teacher resets violations
        $this->actingAs($this->teacherUser)->postJson(route('guru.cbt.monitor.reset-violations', $attempt->id))
            ->assertStatus(200)->assertJson(['success' => true]);

        $this->assertEquals(0, $attempt->violations()->count());
    }

    public function test_teacher_can_grade_essay_and_recalculate_total_score(): void
    {
        $exam = Exam::create([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'title' => 'CBT Essay Test',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
            'duration_minutes' => 60,
            'max_violations' => 3,
            'is_active' => true,
        ]);

        $qEssay = Question::create([
            'exam_id' => $exam->id,
            'type' => 'Esai',
            'content' => 'Jelaskan tujuan puasa Ramadhan.',
            'marks' => 100,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $this->student1->id,
            'answers' => [
                $qEssay->id => ['text_answer' => 'Mencapai derajat taqwa.'],
            ],
            'started_at' => now()->subMinutes(30),
            'finished_at' => now(),
            'score' => 0,
            'needs_manual_grading' => true,
        ]);

        // Teacher grades essay with 90 points
        $res = $this->actingAs($this->teacherUser)->postJson(route('guru.cbt.grading.store', $attempt->id), [
            'question_id' => $qEssay->id,
            'score' => 90,
            'comment' => 'Penjelasan lugas dan tepat.',
        ]);

        $res->assertStatus(200)->assertJson(['success' => true]);

        $attempt->refresh();
        $this->assertEquals(90.0, $attempt->score);
        $this->assertFalse($attempt->needs_manual_grading);
    }
}
