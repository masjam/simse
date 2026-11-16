<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\Badge;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Lesson;
use App\Models\Option;
use App\Models\Question;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\SystemSetting;
use App\Models\Teacher;
use App\Models\User;
use App\Services\Gamification\PointService;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Sprint5BGamificationTest extends TestCase
{
    use RefreshDatabase;

    private User $studentUser;

    private Student $student;

    private User $secondStudentUser;

    private Student $secondStudent;

    private User $teacherUser;

    private Teacher $teacher;

    private User $adminUser;

    private StudentClass $class;

    private Subject $subject;

    private Lesson $lesson;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Guru Pengajar', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Orang Tua', 'guard_name' => 'web']);

        $this->seed(BadgeSeeder::class);

        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $this->adminUser = User::factory()->create(['name' => 'Admin Sekolah']);
        $this->adminUser->assignRole('Super Admin');

        $this->teacherUser = User::factory()->create(['name' => 'Ustadz Hasan']);
        $this->teacherUser->assignRole('Guru Pengajar');
        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'nip' => '198801012015011001',
        ]);

        $this->class = StudentClass::create([
            'academic_year_id' => $academicYear->id,
            'name' => '5 Umar bin Khattab',
            'level' => '5',
            'teacher_id' => $this->teacher->id,
        ]);

        $this->subject = Subject::create([
            'name' => 'Matematika Terpadu',
            'code' => 'MTK-5',
        ]);

        $this->studentUser = User::factory()->create(['name' => 'Ahmad Fatih']);
        $this->studentUser->assignRole('Siswa');
        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'student_class_id' => $this->class->id,
            'nis' => '202605001',
            'nisn' => '0158819001',
            'nik' => '3403011101150001',
            'gender' => 'L',
        ]);

        $this->secondStudentUser = User::factory()->create(['name' => 'Zahra Amira']);
        $this->secondStudentUser->assignRole('Siswa');
        $this->secondStudent = Student::create([
            'user_id' => $this->secondStudentUser->id,
            'student_class_id' => $this->class->id,
            'nis' => '202605002',
            'nisn' => '0158819002',
            'nik' => '3403011101150002',
            'gender' => 'P',
        ]);

        $this->lesson = Lesson::create([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'student_class_id' => $this->class->id,
            'title' => 'Pecahan dan Desimal',
            'content' => 'Materi operasi hitung pecahan.',
            'order' => 1,
        ]);
    }

    public function test_student_earns_points_and_first_step_badge_on_submitting_first_assignment(): void
    {
        $assignment = Assignment::create([
            'lesson_id' => $this->lesson->id,
            'title' => 'Latihan Soal Pecahan Mandiri',
            'description' => 'Kerjakan 5 soal di buku tulis.',
            'due_date' => now()->addDays(2),
            'max_score' => 100,
        ]);

        $file = UploadedFile::fake()->create('jawaban.pdf', 200, 'application/pdf');

        $response = $this->actingAs($this->studentUser)
            ->post(route('siswa.tugas.submit', $assignment->id), [
                'text_answer' => 'Jawaban saya nomor 1-5 sudah terlampir Ustadz.',
                'file' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert 50 points awarded
        $this->assertDatabaseHas('point_events', [
            'student_id' => $this->student->id,
            'event_type' => 'assignment_submitted',
            'points' => 50,
        ]);

        // Assert first_step badge unlocked
        $firstStepBadge = Badge::where('code', 'first_step')->firstOrFail();
        $this->assertDatabaseHas('student_badges', [
            'student_id' => $this->student->id,
            'badge_id' => $firstStepBadge->id,
        ]);
    }

    public function test_submitting_three_assignments_unlocks_diligent_badge(): void
    {
        $pointService = app(PointService::class);

        for ($i = 1; $i <= 3; $i++) {
            $assignment = Assignment::create([
                'lesson_id' => $this->lesson->id,
                'title' => "Tugas Harian {$i}",
                'due_date' => now()->addDays(3),
                'max_score' => 100,
            ]);

            $this->actingAs($this->studentUser)
                ->post(route('siswa.tugas.submit', $assignment->id), [
                    'text_answer' => "Jawaban tugas ke-{$i}",
                ]);
        }

        $diligentBadge = Badge::where('code', 'diligent_student')->firstOrFail();
        $this->assertDatabaseHas('student_badges', [
            'student_id' => $this->student->id,
            'badge_id' => $diligentBadge->id,
        ]);

        // Total points should be at least 3 * 50 = 150
        $totalPoints = $this->student->pointEvents()->sum('points');
        $this->assertGreaterThanOrEqual(150, $totalPoints);
    }

    public function test_teacher_grading_with_score_above_ninety_awards_score_master_badge(): void
    {
        $assignment = Assignment::create([
            'lesson_id' => $this->lesson->id,
            'title' => 'Tugas Matematika Olimpiade',
            'due_date' => now()->addDays(2),
            'max_score' => 100,
        ]);

        $submission = $assignment->submissions()->create([
            'student_id' => $this->student->id,
            'text_answer' => 'Jawaban lengkap saya',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->post(route('guru.tugas.grade', $submission->id), [
                'score' => 95,
                'feedback' => 'Mumtaz, pemahaman konsep sangat kuat!',
            ]);

        $response->assertRedirect();

        // Check bonus points for high score
        $this->assertDatabaseHas('point_events', [
            'student_id' => $this->student->id,
            'event_type' => 'high_score',
        ]);

        // Check score_master badge unlocked
        $scoreMasterBadge = Badge::where('code', 'score_master')->firstOrFail();
        $this->assertDatabaseHas('student_badges', [
            'student_id' => $this->student->id,
            'badge_id' => $scoreMasterBadge->id,
        ]);
    }

    public function test_cbt_completion_awards_points_and_cbt_champion_badge(): void
    {
        $exam = Exam::create([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'title' => 'Kuis Harian Operasi Pecahan',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
            'duration_minutes' => 60,
            'is_active' => true,
        ]);

        $q = Question::create([
            'exam_id' => $exam->id,
            'type' => 'Pilihan Ganda',
            'content' => 'Berapa hasil dari 1/2 + 1/2?',
            'marks' => 100,
        ]);

        $opt = Option::create([
            'question_id' => $q->id,
            'content' => '1',
            'is_correct' => true,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $this->student->id,
            'started_at' => now()->subMinutes(20),
            'answers' => [$q->id => ['option_id' => $opt->id]],
        ]);

        $response = $this->actingAs($this->studentUser)
            ->post(route('siswa.cbt.submit', $exam->id), [
                'answers' => [$q->id => ['option_id' => $opt->id]],
            ]);

        $response->assertRedirect();

        // Assert points awarded
        $this->assertDatabaseHas('point_events', [
            'student_id' => $this->student->id,
            'event_type' => 'cbt_completed',
        ]);

        // Score was 92 >= 90, so cbt_high_score also awarded
        $this->assertDatabaseHas('point_events', [
            'student_id' => $this->student->id,
            'event_type' => 'cbt_high_score',
        ]);

        $cbtBadge = Badge::where('code', 'cbt_champion')->firstOrFail();
        $this->assertDatabaseHas('student_badges', [
            'student_id' => $this->student->id,
            'badge_id' => $cbtBadge->id,
        ]);
    }

    public function test_reaching_five_hundred_points_advances_level_and_unlocks_mujahidin_star(): void
    {
        $pointService = app(PointService::class);

        // Award 520 points directly
        $pointService->award($this->student, 'manual_reward', null, 'Prestasi gemilang', 520);

        $profile = $pointService->getStudentProfile($this->student);

        $this->assertEquals(520, $profile['total_points']);
        $this->assertGreaterThanOrEqual(4, $profile['level']['level']);
        $this->assertEquals('Pendekar Cilik Al Mujahidin', $profile['level']['title']);

        $starBadge = Badge::where('code', 'mujahidin_star')->firstOrFail();
        $this->assertDatabaseHas('student_badges', [
            'student_id' => $this->student->id,
            'badge_id' => $starBadge->id,
        ]);
    }

    public function test_leaderboard_ranks_students_by_total_points(): void
    {
        $pointService = app(PointService::class);

        // Student 1 gets 150 points
        $pointService->award($this->student, 'manual_reward', null, 'Poin Fatih', 150);

        // Student 2 gets 350 points
        $pointService->award($this->secondStudent, 'manual_reward', null, 'Poin Zahra', 350);

        $leaderboard = $pointService->getLeaderboard($this->class->id);

        $this->assertCount(2, $leaderboard);
        $this->assertEquals('Zahra Amira', $leaderboard[0]['name']);
        $this->assertEquals(1, $leaderboard[0]['rank']);
        $this->assertEquals('🥇', $leaderboard[0]['medal']);
        $this->assertEquals(350, $leaderboard[0]['total_points']);

        $this->assertEquals('Ahmad Fatih', $leaderboard[1]['name']);
        $this->assertEquals(2, $leaderboard[1]['rank']);
        $this->assertEquals('🥈', $leaderboard[1]['medal']);
        $this->assertEquals(150, $leaderboard[1]['total_points']);
    }

    public function test_student_lms_page_contains_gamification_profile_and_leaderboard(): void
    {
        $pointService = app(PointService::class);
        $pointService->award($this->student, 'manual_reward', null, 'Poin Awal', 100);

        $response = $this->actingAs($this->studentUser)->get(route('siswa.lms'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Student/LMS')
            ->has('gamification')
            ->where('gamification.is_enabled', true)
            ->where('gamification.total_xp', 100)
            ->has('gamification.level')
            ->has('gamification.badges')
            ->has('gamification.leaderboard')
        );
    }

    public function test_super_admin_can_toggle_gamification_off(): void
    {
        $pointService = app(PointService::class);
        $this->assertTrue($pointService->isEnabled());

        // Update setting to disabled ('0')
        $response = $this->actingAs($this->adminUser)
            ->post(route('sistem.pengaturan.update'), [
                'school_name' => 'SD Muhammadiyah Al Mujahidin',
                'school_address' => 'Wonosari',
                'active_semester' => 'Ganjil 2026/2027',
                'gamification_enabled' => '0',
            ]);

        $response->assertRedirect();
        $this->assertEquals('0', SystemSetting::getValue('gamification_enabled'));
        $this->assertFalse($pointService->isEnabled());

        // Awarding points when disabled returns null and saves no events
        $result = $pointService->award($this->student, 'assignment_submitted', null, 'Test');
        $this->assertNull($result);
        $this->assertDatabaseCount('point_events', 0);

        // Leaderboard returns empty array
        $this->assertEmpty($pointService->getLeaderboard());

        // LMS Page reports is_enabled = false
        $lmsResponse = $this->actingAs($this->studentUser)->get(route('siswa.lms'));
        $lmsResponse->assertStatus(200);
        $lmsResponse->assertInertia(fn ($page) => $page
            ->component('Student/LMS')
            ->where('gamification.is_enabled', false)
        );
    }
}
