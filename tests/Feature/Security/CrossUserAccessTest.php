<?php

namespace Tests\Feature\Security;

use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\Bill;
use App\Models\BillType;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Lesson;
use App\Models\Payment;
use App\Models\Question;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * CrossUserAccessTest — membuktikan isolasi data antar-pengguna (IDOR prevention).
 * Setiap test: pengguna A mencoba mengakses/mengubah milik pengguna B → 403 atau 404.
 * Mencakup 18 route ≥ minimum 15 yang disyaratkan Sprint 0.
 */
class CrossUserAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $studentUserA;

    private User $studentUserB;

    private Student $studentA;

    private Student $studentB;

    private User $parentUserA;

    private User $parentUserB;

    private User $teacherUserA;

    private User $teacherUserB;

    private Teacher $teacherA;

    private Teacher $teacherB;

    private User $bendaharaUser;

    private User $regularUser;

    private StudentClass $classA;

    private StudentClass $classB;

    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');
        Storage::fake('local');

        foreach (['Siswa', 'Orang Tua', 'Guru Pengajar', 'Bendahara', 'Admin'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $year = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $this->subject = Subject::create(['name' => 'Matematika', 'code' => 'MTK']);

        $this->classA = StudentClass::create(['name' => '5A', 'level' => '5', 'academic_year_id' => $year->id]);
        $this->classB = StudentClass::create(['name' => '5B', 'level' => '5', 'academic_year_id' => $year->id]);

        // Siswa A
        $this->studentUserA = User::factory()->create(['name' => 'Siswa A', 'email' => 'siswa_a@test.com']);
        $this->studentUserA->assignRole('Siswa');
        $this->studentA = Student::create([
            'user_id' => $this->studentUserA->id,
            'student_class_id' => $this->classA->id,
            'nisn' => '0011111111',
            'nik' => '3403010101010001',
        ]);

        // Siswa B
        $this->studentUserB = User::factory()->create(['name' => 'Siswa B', 'email' => 'siswa_b@test.com']);
        $this->studentUserB->assignRole('Siswa');
        $this->studentB = Student::create([
            'user_id' => $this->studentUserB->id,
            'student_class_id' => $this->classB->id,
            'nisn' => '0022222222',
            'nik' => '3403010101010002',
        ]);

        // Orang Tua A & B
        $this->parentUserA = User::factory()->create(['name' => 'Ortu A', 'email' => 'ortu_a@test.com']);
        $this->parentUserA->assignRole('Orang Tua');
        $this->studentA->update(['parent_user_id' => $this->parentUserA->id]);

        $this->parentUserB = User::factory()->create(['name' => 'Ortu B', 'email' => 'ortu_b@test.com']);
        $this->parentUserB->assignRole('Orang Tua');
        $this->studentB->update(['parent_user_id' => $this->parentUserB->id]);

        // Guru A
        $this->teacherUserA = User::factory()->create(['name' => 'Guru A', 'email' => 'guru_a@test.com']);
        $this->teacherUserA->assignRole('Guru Pengajar');
        $this->teacherA = Teacher::create(['user_id' => $this->teacherUserA->id, 'nip' => '111111111111111111']);

        // Guru B
        $this->teacherUserB = User::factory()->create(['name' => 'Guru B', 'email' => 'guru_b@test.com']);
        $this->teacherUserB->assignRole('Guru Pengajar');
        $this->teacherB = Teacher::create(['user_id' => $this->teacherUserB->id, 'nip' => '222222222222222222']);

        // Bendahara
        $this->bendaharaUser = User::factory()->create(['name' => 'Bendahara', 'email' => 'bendahara@test.com']);
        $this->bendaharaUser->assignRole('Bendahara');

        // Pengguna biasa
        $this->regularUser = User::factory()->create(['name' => 'Regular', 'email' => 'regular@test.com']);
    }

    private function makeLessonB(array $extra = []): Lesson
    {
        return Lesson::create(array_merge([
            'teacher_id' => $this->teacherB->id,
            'subject_id' => $this->subject->id,
            'student_class_id' => $this->classB->id,
            'title' => 'Materi Guru B',
            'content' => 'Konten materi kelas B',
            'order' => 1,
        ], $extra));
    }

    // ── 1. Siswa A tidak bisa download submission milik Siswa B ──────────

    public function test_siswa_cannot_download_another_students_submission(): void
    {
        $lessonB = $this->makeLessonB();

        $assignmentB = Assignment::create([
            'lesson_id' => $lessonB->id,
            'title' => 'Tugas IPA',
            'description' => 'Kerjakan soal',
            'due_date' => now()->addDays(7),
        ]);

        Storage::disk('local')->put('submissions/file_b.pdf', 'isi file');

        $submissionB = Submission::create([
            'assignment_id' => $assignmentB->id,
            'student_id' => $this->studentB->id,
            'file_path' => 'submissions/file_b.pdf',
        ]);

        $this->actingAs($this->studentUserA)
            ->get(route('siswa.submissions.download', $submissionB))
            ->assertStatus(403);
    }

    // ── 2. Orang Tua A tidak bisa download sertifikat prestasi siswa B ────

    public function test_ortu_cannot_download_another_students_achievement_certificate(): void
    {
        Storage::disk('local')->put('achievements/sertif_b.pdf', 'sertifikat siswa b');

        $achievementB = StudentAchievement::create([
            'student_id' => $this->studentB->id,
            'name' => 'Juara 1 OSN Matematika',
            'category' => 'Akademik',
            'level' => 'Kabupaten/Kota',
            'year' => 2026,
            'organizer' => 'Kemdikbud',
            'achievement_date' => '2026-05-10',
            'certificate_path' => 'achievements/sertif_b.pdf',
            'certificate_original_name' => 'sertif_b.pdf',
            'status' => 'Disetujui',
            'submitted_by' => $this->parentUserB->id,
        ]);

        $this->actingAs($this->parentUserA)
            ->get(route('ortu.achievements.certificate', $achievementB))
            ->assertStatus(403);
    }

    // ── 3. Siswa A tidak bisa save jawaban attempt milik Siswa B ─────────

    public function test_siswa_cannot_save_answer_for_another_students_attempt(): void
    {
        $exam = Exam::create([
            'teacher_id' => $this->teacherB->id,
            'subject_id' => $this->subject->id,
            'title' => 'UAS IPA',
            'duration_minutes' => 90,
            'is_active' => true,
            'start_time' => now()->subMinutes(5),
            'end_time' => now()->addHour(),
        ]);

        $question = Question::create([
            'exam_id' => $exam->id,
            'content' => 'Berapa 1+1?',
            'type' => 'Pilihan Ganda',
            'marks' => 10,
        ]);

        ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $this->studentB->id,
            'started_at' => now(),
        ]);

        $this->actingAs($this->studentUserA)
            ->post(route('siswa.cbt.answer', $exam), [
                'question_id' => $question->id,
                'text_answer' => '2',
            ])
            ->assertStatus(404);
    }

    // ── 4. Siswa A tidak bisa submit ujian siswa B ────────────────────────

    public function test_siswa_cannot_submit_another_students_exam(): void
    {
        $exam = Exam::create([
            'teacher_id' => $this->teacherB->id,
            'subject_id' => $this->subject->id,
            'title' => 'UAS Bahasa Indonesia',
            'duration_minutes' => 90,
            'is_active' => true,
            'start_time' => now()->subMinutes(5),
            'end_time' => now()->addHour(),
        ]);

        ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $this->studentB->id,
            'started_at' => now(),
        ]);

        $this->actingAs($this->studentUserA)
            ->post(route('siswa.cbt.submit', $exam))
            ->assertStatus(404);
    }

    // ── 5. Siswa A tidak bisa download attachment dari kelas lain ─────────

    public function test_siswa_cannot_download_lesson_attachment_from_another_class(): void
    {
        Storage::disk('private')->put('lessons/materi_b.pdf', 'isi materi');

        $lessonB = $this->makeLessonB(['attachment' => 'lessons/materi_b.pdf']);

        $this->actingAs($this->studentUserA)
            ->get(route('siswa.lms.attachment', $lessonB))
            ->assertStatus(403);
    }

    // ── 6. Orang Tua A tidak bisa switch ke anak Orang Tua B ─────────────

    public function test_ortu_cannot_switch_to_another_parents_child(): void
    {
        $this->actingAs($this->parentUserA)
            ->post(route('ortu.switch-child'), [
                'student_id' => $this->studentB->id,
            ])
            ->assertStatus(403);
    }

    // ── 7. Orang Tua A tidak bisa update limit dompet anak lain ──────────

    public function test_ortu_cannot_update_another_childs_wallet_limit(): void
    {
        Wallet::create(['student_id' => $this->studentB->id, 'balance' => 50000]);

        $this->actingAs($this->parentUserA)
            ->post(route('ortu.wallet.limit'), [
                'student_id' => $this->studentB->id,
                'daily_limit' => 20000,
            ])
            ->assertStatus(403);
    }

    // ── 8. Siswa tidak bisa akses halaman kasir Bendahara ────────────────

    public function test_siswa_cannot_access_bendahara_kasir(): void
    {
        $this->actingAs($this->studentUserA)
            ->get(route('bendahara.kasir'))
            ->assertStatus(403);
    }

    // ── 9. Orang Tua tidak bisa download struk siswa lain ───────────────

    public function test_ortu_cannot_download_another_students_payment_receipt(): void
    {
        $billType = BillType::create(['name' => 'SPP', 'amount' => 150000, 'period' => 'Bulanan']);
        $bill = Bill::create([
            'student_id' => $this->studentB->id,
            'bill_type_id' => $billType->id,
            'amount_due' => 150000,
            'status' => 'Belum Lunas',
            'month' => 'November',
            'year' => '2026',
        ]);

        Storage::disk('private')->put('receipts/struk_b.pdf', 'isi struk');

        $payment = Payment::create([
            'bill_id' => $bill->id,
            'user_id' => $this->bendaharaUser->id,
            'amount_paid' => 150000,
            'payment_method' => 'Tunai',
            'payment_date' => now()->toDateString(),
            'status' => 'Berhasil',
            'receipt_photo' => 'receipts/struk_b.pdf',
        ]);

        $this->actingAs($this->parentUserA)
            ->get(route('bendahara.payments.receipt', $payment))
            ->assertStatus(403);
    }

    // ── 10. Guru A tidak bisa download attachment pelajaran Guru B ────────

    public function test_guru_cannot_download_another_teachers_lesson_attachment(): void
    {
        Storage::disk('private')->put('lessons/materi_guru_b.pdf', 'isi materi');

        $lessonB = $this->makeLessonB(['attachment' => 'lessons/materi_guru_b.pdf']);

        $this->actingAs($this->teacherUserA)
            ->get(route('guru.lms.attachment', $lessonB))
            ->assertStatus(403);
    }

    // ── 11. Guru A tidak bisa hapus lesson milik Guru B ──────────────────

    public function test_guru_cannot_delete_another_teachers_lesson(): void
    {
        $lessonB = $this->makeLessonB();

        $this->actingAs($this->teacherUserA)
            ->delete(route('guru.lms.destroy', $lessonB))
            ->assertStatus(403);
    }

    // ── 12. Guru A tidak bisa update jurnal Guru B ────────────────────────

    public function test_guru_cannot_update_another_teachers_journal(): void
    {
        $journalB = TeachingJournal::create([
            'teacher_id' => $this->teacherB->id,
            'subject_id' => $this->subject->id,
            'student_class_id' => $this->classB->id,
            'date' => now()->toDateString(),
            'topic' => 'Materi B',
            'summary' => 'Ringkasan B',
            'notes' => 'Catatan B',
        ]);

        $this->actingAs($this->teacherUserA)
            ->put(route('guru.jurnal.update', $journalB), [
                'student_class_id' => $this->classB->id,
                'subject_id' => $this->subject->id,
                'date' => now()->toDateString(),
                'topic' => 'Diubah Guru A',
                'summary' => 'Ringkasan diubah',
                'notes' => 'Catatan baru',
            ])
            ->assertStatus(403);
    }

    // ── 13. Guru A tidak bisa nilai submission tugas Guru B ───────────────

    public function test_guru_cannot_grade_submission_of_another_teachers_assignment(): void
    {
        $lessonB = $this->makeLessonB();

        $assignmentB = Assignment::create([
            'lesson_id' => $lessonB->id,
            'title' => 'Tugas Guru B',
            'description' => 'Kerjakan soal',
            'due_date' => now()->addDays(5),
        ]);

        $submissionB = Submission::create([
            'assignment_id' => $assignmentB->id,
            'student_id' => $this->studentB->id,
        ]);

        $this->actingAs($this->teacherUserA)
            ->post(route('guru.tugas.grade', $submissionB), [
                'score' => 90,
                'feedback' => 'Bagus',
            ])
            ->assertStatus(403);
    }

    // ── 14. Siswa tidak bisa hapus tagihan SPP ───────────────────────────

    public function test_siswa_cannot_destroy_spp_bill(): void
    {
        $billType = BillType::create(['name' => 'SPP Tahun 2027', 'amount' => 200000, 'period' => 'Bulanan']);
        $bill = Bill::create([
            'student_id' => $this->studentA->id,
            'bill_type_id' => $billType->id,
            'amount_due' => 200000,
            'status' => 'Belum Lunas',
            'month' => 'Januari',
            'year' => '2027',
        ]);

        $this->actingAs($this->studentUserA)
            ->delete(route('bendahara.spp.bills.destroy', $bill))
            ->assertStatus(403);
    }

    // ── 15. Pengguna biasa tidak bisa akses halaman Admin ────────────────

    public function test_regular_user_cannot_access_admin_ppdb(): void
    {
        $this->actingAs($this->regularUser)
            ->get(route('admin.ppdb.index'))
            ->assertStatus(403);
    }

    // ── 16. Siswa tidak bisa akses halaman payroll guru ──────────────────

    public function test_siswa_cannot_access_teacher_payroll_page(): void
    {
        $this->actingAs($this->studentUserA)
            ->get(route('guru.payroll'))
            ->assertStatus(403);
    }

    // ── 17. Orang Tua tidak bisa akses dashboard Bendahara ───────────────

    public function test_ortu_cannot_access_bendahara_dashboard(): void
    {
        $this->actingAs($this->parentUserA)
            ->get(route('bendahara.dashboard'))
            ->assertStatus(403);
    }

    // ── 18. Guru tidak bisa hapus bill type ──────────────────────────────

    public function test_guru_cannot_destroy_bill_type(): void
    {
        $billType = BillType::create(['name' => 'Dana Sosial', 'amount' => 50000, 'period' => 'Sekali Bayar']);

        $this->actingAs($this->teacherUserA)
            ->delete(route('bendahara.spp.bill-types.destroy', $billType))
            ->assertStatus(403);
    }
}
