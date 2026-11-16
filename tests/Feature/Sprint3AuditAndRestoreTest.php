<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\Bill;
use App\Models\BillType;
use App\Models\Grade;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint3AuditAndRestoreTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $teacherUser;

    private Student $student;

    private Subject $subject;

    private Teacher $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Guru Pengajar', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Bendahara', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);

        $this->admin = User::factory()->create(['name' => 'Super Admin']);
        $this->admin->assignRole('Super Admin');

        $this->teacherUser = User::factory()->create(['name' => 'Ustadzah Khadijah']);
        $this->teacherUser->assignRole('Guru Pengajar');

        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'nip' => '198701012010012002',
        ]);

        $year = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $class = StudentClass::create([
            'name' => '2 Al-Battani',
            'level' => '2',
            'teacher_id' => $this->teacher->id,
            'academic_year_id' => $year->id,
        ]);

        $studentUser = User::factory()->create(['name' => 'Hafizh Ahmad']);
        $studentUser->assignRole('Siswa');

        $this->student = Student::create([
            'user_id' => $studentUser->id,
            'student_class_id' => $class->id,
            'nis' => '202602001',
            'nisn' => '0148923842',
            'nik' => '3403010101180002',
            'no_telp_ortu' => '081234567899',
            'gender' => 'L',
        ]);

        $this->subject = Subject::create([
            'name' => 'Matematika',
            'code' => 'MTK',
        ]);
    }

    public function test_grade_creation_and_update_records_audit_log_accurately(): void
    {
        $this->actingAs($this->teacherUser);

        $grade = Grade::create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'type' => 'UTS',
            'name' => 'Ujian Tengah Semester 1',
            'score' => 85,
            'notes' => 'Sangat baik',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'created',
            'auditable_type' => Grade::class,
            'auditable_id' => $grade->id,
            'user_id' => $this->teacherUser->id,
        ]);

        // Now update the grade score
        $grade->update([
            'score' => 95,
            'notes' => 'Revisi setelah penilaian ulang',
        ]);

        $updateLog = AuditLog::where('auditable_type', Grade::class)
            ->where('auditable_id', $grade->id)
            ->where('event', 'updated')
            ->first();

        $this->assertNotNull($updateLog);
        $this->assertEquals(85, $updateLog->old_values['score']);
        $this->assertEquals(95, $updateLog->new_values['score']);
    }

    public function test_sensitive_attributes_like_password_and_pin_are_excluded_from_audit_logs(): void
    {
        $this->actingAs($this->admin);

        $user = User::factory()->create([
            'name' => 'Testing User',
            'email' => 'test@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $userLog = AuditLog::where('auditable_type', User::class)
            ->where('auditable_id', $user->id)
            ->first();

        $this->assertNotNull($userLog);
        $this->assertArrayNotHasKey('password', $userLog->new_values ?? []);
        $this->assertArrayNotHasKey('remember_token', $userLog->new_values ?? []);
    }

    public function test_super_admin_can_access_audit_log_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.audit.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Admin/Audit/Index'));
    }

    public function test_non_admin_cannot_access_audit_log_page(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('admin.audit.index'));
        $response->assertStatus(403);
    }

    public function test_soft_deletes_on_student_and_restoration_via_trash(): void
    {
        $studentId = $this->student->id;

        // Soft delete the student
        $this->student->delete();

        // Should not appear in normal query
        $this->assertNull(Student::find($studentId));
        // Should appear in onlyTrashed
        $this->assertNotNull(Student::onlyTrashed()->find($studentId));

        // Restore via Super Admin Trash endpoint
        $response = $this->actingAs($this->admin)->post(route('admin.trash.restore', [
            'type' => 'student',
            'id' => $studentId,
        ]));

        $response->assertRedirect();
        $this->assertNotNull(Student::find($studentId));
        $this->assertNull(Student::find($studentId)->deleted_at);
    }

    public function test_payment_cancellation_sets_batal_status_and_recalculates_bill(): void
    {
        $this->admin->assignRole('Bendahara');

        $billType = BillType::create(['name' => 'SPP Oktober 2026', 'amount' => 300000, 'period' => 'Bulanan']);
        $bill = Bill::create([
            'bill_type_id' => $billType->id,
            'student_id' => $this->student->id,
            'amount_due' => 300000,
            'status' => 'Lunas',
        ]);

        $payment = Payment::create([
            'bill_id' => $bill->id,
            'user_id' => $this->admin->id,
            'amount_paid' => 300000,
            'payment_method' => 'Tunai',
            'payment_date' => now()->toDateString(),
            'status' => 'Berhasil',
        ]);

        // Cancel the payment
        $response = $this->actingAs($this->admin)->post(route('bendahara.payments.cancel', [
            'payment' => $payment->id,
        ]), [
            'reason' => 'Salah input pos pembayaran oleh kasir',
        ]);

        $response->assertRedirect();

        $payment->refresh();
        $bill->refresh();

        $this->assertEquals('Batal', $payment->status);
        $this->assertNotNull($payment->canceled_at);
        $this->assertEquals($this->admin->id, $payment->canceled_by);
        $this->assertEquals('Salah input pos pembayaran oleh kasir', $payment->cancel_reason);

        // Bill should revert to Belum Lunas
        $this->assertEquals('Belum Lunas', $bill->status);
    }

    public function test_digital_wallet_payment_cancellation_returns_funds_to_student_wallet(): void
    {
        $this->admin->assignRole('Bendahara');

        $wallet = Wallet::create([
            'student_id' => $this->student->id,
            'balance' => 50000, // Remaining after SPP was paid
        ]);

        $billType = BillType::create(['name' => 'SPP November 2026', 'amount' => 200000, 'period' => 'Bulanan']);
        $bill = Bill::create([
            'bill_type_id' => $billType->id,
            'student_id' => $this->student->id,
            'amount_due' => 200000,
            'status' => 'Lunas',
        ]);

        $payment = Payment::create([
            'bill_id' => $bill->id,
            'user_id' => $this->admin->id,
            'amount_paid' => 200000,
            'payment_method' => 'Dompet Digital',
            'payment_date' => now()->toDateString(),
            'status' => 'Berhasil',
        ]);

        // Cancel the payment
        $this->actingAs($this->admin)->post(route('bendahara.payments.cancel', [
            'payment' => $payment->id,
        ]), [
            'reason' => 'Permintaan orang tua untuk bayar via transfer bank',
        ]);

        $wallet->refresh();
        // Balance should be refunded: 50,000 + 200,000 = 250,000
        $this->assertEquals(250000, $wallet->balance);

        // Reversal wallet transaction should be recorded
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'Setoran',
            'amount' => 200000,
            'status' => 'Sukses',
        ]);
    }

    public function test_wallet_transaction_cancellation_reverses_balance_correctly(): void
    {
        $this->admin->assignRole('Bendahara');

        $wallet = Wallet::create([
            'student_id' => $this->student->id,
            'balance' => 100000,
        ]);

        $depositTx = WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'Setoran',
            'amount' => 50000,
            'status' => 'Sukses',
            'description' => 'Topup tunai kasir',
        ]);

        // Balance was 100,000 + 50,000 = 150,000
        $wallet->increment('balance', 50000);
        $this->assertEquals(150000, $wallet->fresh()->balance);

        // Cancel the deposit transaction
        $response = $this->actingAs($this->admin)->from(route('bendahara.dompet'))->post(route('bendahara.dompet.cancel', [
            'transaction' => $depositTx->id,
        ]), [
            'reason' => 'Uang fisik belum diterima / pembatalan kasir',
        ]);
        if ($response->status() !== 302) {
            dump($response->status(), $response->exception?->getMessage() ?? substr($response->getContent(), 0, 300));
        }
        if (session('errors')) {
            dump(session('errors')->all());
        }

        $depositTx->refresh();
        $this->assertEquals('Batal', $depositTx->status);
        $this->assertNotNull($depositTx->canceled_at);

        // Balance should be reverted back to 100,000
        $this->assertEquals(100000, $wallet->fresh()->balance);
    }
}
