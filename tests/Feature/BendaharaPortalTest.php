<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Bill;
use App\Models\BillType;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\PayrollComponent;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingSchedule;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BendaharaPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $bendaharaUser;

    private StudentClass $class;

    private Student $student;

    private Teacher $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Bendahara', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Guru Pengajar', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);

        $this->bendaharaUser = User::factory()->create([
            'name' => 'Ayu Bendahara',
            'email' => 'bendahara@test.com',
        ]);
        $this->bendaharaUser->assignRole('Bendahara');

        $year = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $this->class = StudentClass::create([
            'name' => '6A',
            'level' => '6',
            'academic_year_id' => $year->id,
        ]);

        $studentUser = User::factory()->create(['name' => 'Budi Santoso']);
        $studentUser->assignRole('Siswa');
        $this->student = Student::create([
            'user_id' => $studentUser->id,
            'student_class_id' => $this->class->id,
            'nisn' => '0012345678',
            'nik' => '3403010101010001',
            'no_telp_ortu' => '081234567890',
        ]);

        $teacherUser = User::factory()->create(['name' => 'Siti Guru']);
        $teacherUser->assignRole('Guru Pengajar');
        $this->teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'nip' => '198407152010012015',
        ]);
    }

    public function test_guest_and_non_bendahara_cannot_access_bendahara_portal(): void
    {
        $this->get(route('bendahara.dashboard'))
            ->assertRedirect(route('role.login', ['role' => 'bendahara']));

        $siswaUser = User::factory()->create();
        $siswaUser->assignRole('Siswa');

        $this->actingAs($siswaUser)
            ->get(route('bendahara.dashboard'))
            ->assertForbidden();
    }

    public function test_bendahara_can_access_all_module_pages(): void
    {
        $this->actingAs($this->bendaharaUser);

        $this->get(route('bendahara.dashboard'))->assertOk();
        $this->get(route('bendahara.kasir'))->assertOk();
        $this->get(route('bendahara.spp'))->assertOk();
        $this->get(route('bendahara.dompet'))->assertOk();
        $this->get(route('bendahara.payroll'))->assertOk();
    }

    public function test_bendahara_can_manage_bill_types_generate_bills_and_send_wa_reminder(): void
    {
        $this->actingAs($this->bendaharaUser);

        $this->post(route('bendahara.spp.bill-types.store'), [
            'name' => 'SPP Bulanan',
            'amount' => 450000,
            'period' => 'Bulanan',
        ])->assertSessionHas('success');

        $billType = BillType::where('name', 'SPP Bulanan')->first();
        $this->assertNotNull($billType);

        $this->put(route('bendahara.spp.bill-types.update', $billType), [
            'name' => 'SPP Bulanan SD',
            'amount' => 500000,
            'period' => 'Bulanan',
        ])->assertSessionHas('success');

        $this->post(route('bendahara.spp.generate'), [
            'bill_type_id' => $billType->id,
            'student_class_id' => $this->class->id,
            'month' => 'November',
            'year' => '2026',
        ])->assertSessionHas('success');

        // Re-running generate should be idempotent (only 1 bill created per student/period)
        $this->post(route('bendahara.spp.generate'), [
            'bill_type_id' => $billType->id,
            'student_class_id' => $this->class->id,
            'month' => 'November',
            'year' => '2026',
        ])->assertSessionHas('success');

        $this->assertSame(1, Bill::where('student_id', $this->student->id)->count());
        $this->assertDatabaseHas('bills', [
            'bill_type_id' => $billType->id,
            'student_id' => $this->student->id,
            'amount_due' => 500000,
            'status' => 'Belum Lunas',
        ]);

        $this->post(route('bendahara.spp.remind'), [
            'student_id' => $this->student->id,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('whatsapp_logs', [
            'phone_number' => '6281234567890',
            'status' => 'Terkirim',
        ]);
    }

    public function test_bendahara_can_create_semester_bill_type_and_generate_semester_bills(): void
    {
        $this->actingAs($this->bendaharaUser);

        // 1. Create bill type with period Semester
        $this->post(route('bendahara.spp.bill-types.store'), [
            'name' => 'Biaya Kegiatan & Ekstrakurikuler Semester',
            'amount' => 750000,
            'period' => 'Semester',
        ])->assertSessionHas('success');

        $billType = BillType::where('name', 'Biaya Kegiatan & Ekstrakurikuler Semester')->firstOrFail();
        $this->assertSame('Semester', $billType->period);

        // 2. Update bill type
        $this->put(route('bendahara.spp.bill-types.update', $billType), [
            'name' => 'Biaya Ujian & Kegiatan Semester',
            'amount' => 800000,
            'period' => 'Semester',
        ])->assertSessionHas('success');

        $this->assertSame('Semester', $billType->fresh()->period);
        $this->assertEquals(800000, $billType->fresh()->amount);

        // 3. Generate semester bill
        $this->post(route('bendahara.spp.generate'), [
            'bill_type_id' => $billType->id,
            'student_class_id' => $this->class->id,
            'month' => 'Semester Ganjil',
            'year' => '2026/2027',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('bills', [
            'bill_type_id' => $billType->id,
            'student_id' => $this->student->id,
            'month' => 'Semester Ganjil',
            'year' => '2026/2027',
            'amount_due' => 800000,
            'status' => 'Belum Lunas',
        ]);
    }

    public function test_bendahara_can_process_partial_payment_verify_transfer_and_store_receipt_privately(): void
    {
        Storage::fake('private');
        Storage::fake('public');

        $this->actingAs($this->bendaharaUser);

        $billType = BillType::create([
            'name' => 'Uang Gedung',
            'amount' => 1000000,
            'period' => 'Sekali Bayar',
        ]);

        $bill = Bill::create([
            'bill_type_id' => $billType->id,
            'student_id' => $this->student->id,
            'year' => '2026',
            'status' => 'Belum Lunas',
            'amount_due' => 1000000,
        ]);

        // 1. Direct partial payment in cash -> Bill becomes Angsuran
        $this->post(route('bendahara.kasir.pay'), [
            'bill_id' => $bill->id,
            'amount_paid' => 400000,
            'payment_method' => 'Tunai',
        ])->assertSessionHas('success');

        $this->assertSame('Angsuran', $bill->fresh()->status);

        // 2. Pending transfer payment with receipt uploaded to private disk
        $receipt = UploadedFile::fake()->create('bukti-transfer.pdf', 120, 'application/pdf');
        $this->post(route('bendahara.kasir.pay'), [
            'bill_id' => $bill->id,
            'amount_paid' => 600000,
            'payment_method' => 'Transfer Bank',
            'receipt_photo' => $receipt,
            'is_pending_verification' => true,
        ])->assertSessionHas('success');

        $pendingPayment = Payment::where('status', 'Menunggu Verifikasi')->first();
        $this->assertNotNull($pendingPayment);
        $this->assertNotNull($pendingPayment->receipt_photo);
        Storage::disk('private')->assertExists($pendingPayment->receipt_photo);
        Storage::disk('public')->assertMissing($pendingPayment->receipt_photo);

        $this->get(route('bendahara.payments.receipt', $pendingPayment))
            ->assertOk()
            ->assertDownload();

        // Still Angsuran before verification
        $this->assertSame('Angsuran', $bill->fresh()->status);

        // 3. Approve transfer payment -> Bill becomes Lunas
        $this->post(route('bendahara.payments.verify', $pendingPayment), [
            'decision' => 'Berhasil',
        ])->assertSessionHas('success');

        $this->assertSame('Berhasil', $pendingPayment->fresh()->status);
        $this->assertSame('Lunas', $bill->fresh()->status);
    }

    public function test_bendahara_can_manage_student_wallet_and_pay_bill_via_digital_wallet(): void
    {
        $this->actingAs($this->bendaharaUser);

        // Deposit Rp 500.000
        $this->post(route('bendahara.dompet.transaction'), [
            'student_id' => $this->student->id,
            'type' => 'Setoran',
            'amount' => 500000,
            'description' => 'Setoran awal',
        ])->assertSessionHas('success');

        $wallet = Wallet::where('student_id', $this->student->id)->first();
        $this->assertNotNull($wallet);
        $this->assertSame(500000.0, $wallet->balance);

        // Cannot withdraw more than balance
        $this->post(route('bendahara.dompet.transaction'), [
            'student_id' => $this->student->id,
            'type' => 'Penarikan',
            'amount' => 900000,
        ])->assertSessionHasErrors('amount');

        // Set 6-digit PIN
        $this->post(route('bendahara.dompet.pin', $this->student), [
            'pin' => '654321',
        ])->assertSessionHas('success');
        $this->assertTrue(Hash::check('654321', $wallet->fresh()->pin));

        // Pay a bill using Dompet Digital
        $billType = BillType::create([
            'name' => 'SPP November',
            'amount' => 300000,
            'period' => 'Bulanan',
        ]);
        $bill = Bill::create([
            'bill_type_id' => $billType->id,
            'student_id' => $this->student->id,
            'month' => 'November',
            'year' => '2026',
            'status' => 'Belum Lunas',
            'amount_due' => 300000,
        ]);

        $this->post(route('bendahara.kasir.pay'), [
            'bill_id' => $bill->id,
            'amount_paid' => 300000,
            'payment_method' => 'Dompet Digital',
        ])->assertSessionHas('success');

        $this->assertSame('Lunas', $bill->fresh()->status);
        $this->assertSame(200000.0, $wallet->fresh()->balance);
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'Pembayaran SPP',
            'amount' => 300000,
            'status' => 'Sukses',
        ]);
    }

    public function test_bendahara_can_generate_adjust_and_disburse_payroll(): void
    {
        $this->actingAs($this->bendaharaUser);

        $subject = Subject::create(['code' => 'PAI', 'name' => 'Pendidikan Agama Islam']);
        TeachingSchedule::create([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $subject->id,
            'student_class_id' => $this->class->id,
            'day' => 1,
            'jpl' => 4,
        ]);

        Attendance::create([
            'user_id' => $this->teacher->user_id,
            'date' => Carbon::create(2026, 11, 10)->toDateString(),
            'status' => 'Hadir',
        ]);

        $this->post(route('bendahara.payroll.generate'), [
            'month' => 'November',
            'year' => '2026',
            'base_salary' => 3500000,
        ])->assertSessionHas('success');

        $payroll = Payroll::where('teacher_id', $this->teacher->id)
            ->where('month', 'November')
            ->where('year', '2026')
            ->first();

        $this->assertNotNull($payroll);
        // 3,500,000 + (4 * 75,000 = 300,000) + (1 * 25,000 = 25,000) - 100,000 = 3,725,000
        $this->assertSame(3725000.0, $payroll->total_salary);

        // Add bonus component
        $this->post(route('bendahara.payroll.components.store', $payroll), [
            'type' => 'Penerimaan',
            'name' => 'Tunjangan Wali Kelas',
            'amount' => 275000,
        ])->assertSessionHas('success');

        $this->assertSame(4000000.0, $payroll->fresh()->total_salary);

        $bonus = PayrollComponent::where('payroll_id', $payroll->id)
            ->where('name', 'Tunjangan Wali Kelas')
            ->first();
        $this->assertNotNull($bonus);

        // Delete bonus component -> recalculates back
        $this->delete(route('bendahara.payroll.components.destroy', $bonus))
            ->assertSessionHas('success');
        $this->assertSame(3725000.0, $payroll->fresh()->total_salary);

        // Submit approval & disburse
        $this->post(route('bendahara.payroll.submit-approval'), [
            'month' => 'November',
            'year' => '2026',
        ])->assertSessionHas('success');
        $this->assertSame('Menunggu Persetujuan', $payroll->fresh()->status);

        $this->patch(route('bendahara.payroll.status', $payroll), [
            'status' => 'Dibayar',
        ])->assertSessionHas('success');
        $this->assertSame('Dibayar', $payroll->fresh()->status);
    }
}
