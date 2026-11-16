<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Bill;
use App\Models\BillType;
use App\Models\DocumentVerification;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentViolation;
use App\Models\Teacher;
use App\Models\User;
use App\Models\ViolationPoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint3DocumentAndVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $teacherUser;

    private User $parentUser;

    private User $studentUser;

    private User $otherUser;

    private Student $student;

    private StudentClass $class;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Guru', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Guru Pengajar', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Bendahara', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Orang Tua', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);

        $this->admin = User::factory()->create(['name' => 'Super Admin']);
        $this->admin->assignRole('Super Admin');

        $this->teacherUser = User::factory()->create(['name' => 'Ustadz Ahmad, S.Pd.']);
        $this->teacherUser->assignRole('Guru Pengajar');

        $teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'nip' => '198401012010011001',
        ]);

        $year = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $this->class = StudentClass::create([
            'name' => '1 Brilliant',
            'level' => '1',
            'teacher_id' => $teacher->id,
            'academic_year_id' => $year->id,
        ]);

        $this->parentUser = User::factory()->create(['name' => 'Bapak Hartono']);
        $this->parentUser->assignRole('Orang Tua');

        $this->studentUser = User::factory()->create(['name' => 'Fauzi Hartono']);
        $this->studentUser->assignRole('Siswa');

        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'parent_user_id' => $this->parentUser->id,
            'student_class_id' => $this->class->id,
            'nis' => '202601001',
            'nisn' => '0148923841',
            'nik' => '3403010101180001',
            'no_telp_ortu' => '081234567899',
            'gender' => 'L',
        ]);

        $this->otherUser = User::factory()->create(['name' => 'Pengguna Lain']);
    }

    public function test_teacher_can_generate_student_raport_pdf_with_valid_pdf_header(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->get(route('guru.raport.pdf', ['student' => $this->student->id]));

        $response->assertStatus(200);
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $response->assertHeader('Content-Type', 'application/pdf');

        // Verify verification token created
        $this->assertDatabaseHas('document_verifications', [
            'document_type' => 'e-Raport',
            'reference_id' => $this->student->id,
            'recipient_name' => $this->studentUser->name,
        ]);
    }

    public function test_unauthorized_user_cannot_access_student_raport_pdf(): void
    {
        $response = $this->actingAs($this->otherUser)
            ->get(route('guru.raport.pdf', ['student' => $this->student->id]));

        $response->assertStatus(403);
    }

    public function test_teacher_can_generate_surat_peringatan_pdf(): void
    {
        $point = ViolationPoint::create(['name' => 'Terlambat', 'points' => 10, 'category' => 'Sedang']);
        $violation = StudentViolation::create([
            'student_id' => $this->student->id,
            'violation_point_id' => $point->id,
            'description' => 'Terlambat masuk sekolah lebih dari 3 kali berturut-turut.',
            'date' => now()->toDateString(),
            'status' => 'Pending',
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->get(route('guru.bk.sp.pdf', ['violation' => $violation->id]));

        $response->assertStatus(200);
        $this->assertStringStartsWith('%PDF', $response->getContent());

        $this->assertDatabaseHas('document_verifications', [
            'document_type' => 'Surat Peringatan',
            'reference_id' => $violation->id,
        ]);
    }

    public function test_teacher_can_generate_bap_pelanggaran_pdf(): void
    {
        $point = ViolationPoint::create(['name' => 'Kedisiplinan', 'points' => 5, 'category' => 'Ringan']);
        $violation = StudentViolation::create([
            'student_id' => $this->student->id,
            'violation_point_id' => $point->id,
            'description' => 'Tidak mengenakan seragam sesuai ketentuan hari.',
            'date' => now()->toDateString(),
            'status' => 'Pending',
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->get(route('guru.bk.bap.pdf', ['violation' => $violation->id]));

        $response->assertStatus(200);
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_student_and_parent_can_generate_kts_pdf(): void
    {
        $response = $this->actingAs($this->studentUser)
            ->get(route('siswa.kts.pdf'));

        $response->assertStatus(200);
        $this->assertStringStartsWith('%PDF', $response->getContent());

        $this->assertDatabaseHas('document_verifications', [
            'document_type' => 'Kartu Tanda Siswa',
            'recipient_name' => $this->studentUser->name,
        ]);
    }

    public function test_bendahara_and_owner_can_generate_payment_receipt_pdf(): void
    {
        $billType = BillType::create(['name' => 'SPP November 2026', 'amount' => 350000, 'period' => 'Bulanan']);
        $bill = Bill::create([
            'bill_type_id' => $billType->id,
            'student_id' => $this->student->id,
            'amount_due' => 350000,
            'status' => 'Lunas',
        ]);

        $payment = Payment::create([
            'bill_id' => $bill->id,
            'user_id' => $this->admin->id,
            'amount_paid' => 350000,
            'payment_method' => 'Tunai',
            'payment_date' => now()->toDateString(),
            'status' => 'Berhasil',
        ]);

        // Student owner can access receipt
        $response = $this->actingAs($this->studentUser)
            ->get(route('bendahara.payments.pdf', ['payment' => $payment->id]));

        $response->assertStatus(200);
        $this->assertStringStartsWith('%PDF', $response->getContent());

        // Other unauthorized student cannot access
        $otherStudentUser = User::factory()->create(['name' => 'Siswa Lain']);
        $otherStudentUser->assignRole('Siswa');

        $responseOther = $this->actingAs($otherStudentUser)
            ->get(route('bendahara.payments.pdf', ['payment' => $payment->id]));

        $responseOther->assertStatus(403);
    }

    public function test_payroll_slip_pdf_can_be_generated_by_owner_or_bendahara(): void
    {
        $payroll = Payroll::create([
            'user_id' => $this->teacherUser->id,
            'month' => 'November',
            'year' => '2026',
            'total_salary' => 4500000,
            'status' => 'Dibayar',
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->get(route('bendahara.payroll.pdf', ['payroll' => $payroll->id]));

        $response->assertStatus(200);
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_public_verification_route_displays_valid_document_status_with_masked_name(): void
    {
        $verification = DocumentVerification::create([
            'token' => 'VALID_TEST_TOKEN_40_CHARS_LONG_KEY_12345',
            'document_type' => 'e-Raport',
            'title' => 'e-Raport Ganjil 2026/2027',
            'recipient_name' => 'Fauzi Hartono',
            'content_hash' => hash('sha256', 'test_data'),
            'issued_by' => $this->admin->id,
            'issued_at' => now(),
            'is_revoked' => false,
        ]);

        $response = $this->get(route('verifikasi.show', ['token' => $verification->token]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('DocumentVerification/Show')
            ->where('isValid', true)
            ->where('document.document_type', 'e-Raport')
            ->where('document.recipient_masked', 'F***i H*****o')
        );
    }

    public function test_public_verification_displays_revoked_state_when_revoked(): void
    {
        $verification = DocumentVerification::create([
            'token' => 'REVOKED_TEST_TOKEN_40_CHARS_KEY_1234567',
            'document_type' => 'Slip Gaji',
            'title' => 'Slip Gaji November 2026',
            'recipient_name' => 'Budi Santoso',
            'content_hash' => hash('sha256', 'test_data_revoked'),
            'issued_by' => $this->admin->id,
            'issued_at' => now(),
            'is_revoked' => true,
            'revoked_at' => now(),
            'revoked_reason' => 'Koreksi perhitungan komponen tunjangan keluarga',
        ]);

        $response = $this->get(route('verifikasi.show', ['token' => $verification->token]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('DocumentVerification/Show')
            ->where('isValid', false)
            ->where('document.is_revoked', true)
            ->where('document.revoked_reason', 'Koreksi perhitungan komponen tunjangan keluarga')
        );
    }

    public function test_public_verification_displays_invalid_state_for_unknown_token(): void
    {
        $response = $this->get(route('verifikasi.show', ['token' => 'UNKNOWN_TOKEN']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('DocumentVerification/Show')
            ->where('isValid', false)
            ->where('document', null)
        );
    }
}
