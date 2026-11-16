<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Bill;
use App\Models\BillType;
use App\Models\CounselingSession;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudentClass;
use App\Models\StudentViolation;
use App\Models\Teacher;
use App\Models\User;
use App\Models\ViolationPoint;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint5AStudentPortalAndPwaTest extends TestCase
{
    use RefreshDatabase;

    private User $studentUser;

    private Student $student;

    private User $parentUser;

    private User $otherParentUser;

    private Student $otherStudent;

    private User $teacherUser;

    private Teacher $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Guru Pengajar', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Orang Tua', 'guard_name' => 'web']);

        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $this->parentUser = User::factory()->create(['name' => 'Bunda Fatimah']);
        $this->parentUser->assignRole('Orang Tua');

        $this->otherParentUser = User::factory()->create(['name' => 'Ayah Rudi']);
        $this->otherParentUser->assignRole('Orang Tua');

        $this->teacherUser = User::factory()->create(['name' => 'Ustadzah Aisyah']);
        $this->teacherUser->assignRole('Guru Pengajar');
        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'nip' => '198702022012012003',
        ]);

        $class = StudentClass::create([
            'academic_year_id' => $academicYear->id,
            'name' => '4 Abu Bakar',
            'level' => '4',
            'teacher_id' => $this->teacher->id,
        ]);

        // Student 1 (Belongs to parentUser)
        $this->studentUser = User::factory()->create(['name' => 'Muhammad Bilal']);
        $this->studentUser->assignRole('Siswa');
        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'parent_user_id' => $this->parentUser->id,
            'student_class_id' => $class->id,
            'nis' => '202604001',
            'nisn' => '0148821901',
            'nik' => '3403011201180001',
            'gender' => 'L',
        ]);

        // Student 2 (Belongs to otherParentUser)
        $otherStudentUser = User::factory()->create(['name' => 'Aisyah Putri']);
        $otherStudentUser->assignRole('Siswa');
        $this->otherStudent = Student::create([
            'user_id' => $otherStudentUser->id,
            'parent_user_id' => $this->otherParentUser->id,
            'student_class_id' => $class->id,
            'nis' => '202604002',
            'nisn' => '0148821902',
            'nik' => '3403011201180002',
            'gender' => 'P',
        ]);
    }

    public function test_offline_fallback_page_is_accessible_without_authentication(): void
    {
        $response = $this->get(route('offline'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Offline'));
    }

    public function test_student_can_view_keuangan_page_with_wallet_and_bills(): void
    {
        $wallet = Wallet::create([
            'student_id' => $this->student->id,
            'balance' => 75000,
            'daily_limit' => 20000,
            'pin' => bcrypt('123456'),
        ]);

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'Setoran',
            'amount' => 100000,
            'status' => 'Sukses',
            'description' => 'Topup uang saku oleh orang tua',
        ]);

        $billType = BillType::create(['name' => 'SPP November 2026', 'amount' => 250000, 'period' => 'Bulanan']);
        Bill::create([
            'student_id' => $this->student->id,
            'bill_type_id' => $billType->id,
            'amount_due' => 250000,
            'status' => 'Belum Lunas',
            'month' => '11',
            'year' => '2026',
        ]);

        $response = $this->actingAs($this->studentUser)->get(route('siswa.keuangan'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Student/Keuangan')
            ->has('wallet')
            ->where('wallet.balance', 75000)
            ->where('wallet.daily_limit', 20000)
            ->has('transactions', 1)
            ->has('bills', 1)
        );
    }

    public function test_student_wallet_pin_is_never_exposed_in_keuangan_response(): void
    {
        Wallet::create([
            'student_id' => $this->student->id,
            'balance' => 50000,
            'pin' => 'rahasia_pin_123',
        ]);

        $response = $this->actingAs($this->studentUser)->get(route('siswa.keuangan'));
        $response->assertStatus(200);

        // Assert PIN is NOT in response content
        $this->assertStringNotContainsString('rahasia_pin_123', $response->getContent());
        $response->assertInertia(fn ($page) => $page
            ->component('Student/Keuangan')
            ->missing('wallet.pin')
        );
    }

    public function test_student_can_view_verified_achievements_and_positive_guidance(): void
    {
        StudentAchievement::create([
            'student_id' => $this->student->id,
            'name' => 'Juara 1 Lomba Tahfidz Al-Qur\'an Juz 30',
            'category' => 'Keagamaan',
            'level' => 'Kabupaten',
            'year' => 2026,
            'status' => 'Disetujui',
        ]);

        // Unverified achievement should NOT appear
        StudentAchievement::create([
            'student_id' => $this->student->id,
            'name' => 'Peserta Lomba Kaligrafi',
            'category' => 'Seni',
            'level' => 'Sekolah',
            'year' => 2026,
            'status' => 'Menunggu Verifikasi',
        ]);

        // Positive guidance session
        CounselingSession::create([
            'student_id' => $this->student->id,
            'teacher_id' => $this->teacher->id,
            'type' => 'Akademik',
            'date' => now()->toDateString(),
            'problem' => 'Konsultasi minat olimpiade sains',
            'solution' => 'Diberikan buku referensi olimpiade dan motivasi',
            'status' => 'Selesai',
        ]);

        $response = $this->actingAs($this->studentUser)->get(route('siswa.prestasi'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Student/Prestasi')
            ->has('achievements', 1)
            ->where('achievements.0.name', 'Juara 1 Lomba Tahfidz Al-Qur\'an Juz 30')
            ->has('guidanceNotes', 1)
        );
    }

    public function test_student_cannot_view_disciplinary_violations_or_penalty_points(): void
    {
        $point = ViolationPoint::create(['name' => 'Meninggalkan Kelas', 'points' => 15, 'category' => 'Sedang']);
        StudentViolation::create([
            'student_id' => $this->student->id,
            'violation_point_id' => $point->id,
            'description' => 'Meninggalkan kelas saat pelajaran tanpa izin',
            'date' => now()->toDateString(),
            'status' => 'Pending',
        ]);

        $response = $this->actingAs($this->studentUser)->get(route('siswa.prestasi'));
        $response->assertStatus(200);

        // Security assertion: no violations or points in props
        $response->assertInertia(fn ($page) => $page
            ->component('Student/Prestasi')
            ->missing('violations')
            ->missing('violation_points')
            ->missing('sp_status')
        );
        $this->assertStringNotContainsString('Meninggalkan kelas', $response->getContent());
    }

    public function test_student_can_submit_counseling_request_to_guru_bk(): void
    {
        $response = $this->actingAs($this->studentUser)->post(route('siswa.konseling.store'), [
            'category' => 'Belajar',
            'preferred_schedule' => 'Saat jam istirahat pertama',
            'notes' => 'Ustadz, saya ingin bercerita tentang cara mengatur waktu belajar di rumah.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('counseling_requests', [
            'student_id' => $this->student->id,
            'category' => 'Belajar',
            'status' => 'Diajukan',
        ]);
    }

    public function test_student_counseling_request_validation_fails_on_empty_fields(): void
    {
        $response = $this->actingAs($this->studentUser)->post(route('siswa.konseling.store'), [
            'category' => 'KategoriSalah',
            'notes' => 'abc', // too short
        ]);

        $response->assertSessionHasErrors(['category', 'notes']);
        $this->assertDatabaseCount('counseling_requests', 0);
    }

    public function test_parent_can_update_notification_preferences(): void
    {
        $response = $this->actingAs($this->parentUser)->post(route('ortu.notification-preferences.update'), [
            'channel_wa' => true,
            'channel_in_app' => false,
            'notify_attendance' => true,
            'notify_finance' => true,
            'notify_grades' => false,
            'notify_discipline' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('parent_notification_preferences', [
            'user_id' => $this->parentUser->id,
            'channel_wa' => true,
            'channel_in_app' => false,
            'notify_grades' => false,
        ]);
    }

    public function test_parent_can_download_child_raport_pdf(): void
    {
        $response = $this->actingAs($this->parentUser)
            ->get(route('ortu.raport.pdf', $this->student->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_parent_can_download_child_payment_receipt_pdf(): void
    {
        $billType = BillType::create(['name' => 'Infaq Pembangunan', 'amount' => 500000, 'period' => 'Sekali Bayar']);
        $bill = Bill::create([
            'student_id' => $this->student->id,
            'bill_type_id' => $billType->id,
            'amount_due' => 500000,
            'status' => 'Lunas',
            'month' => '11',
            'year' => '2026',
        ]);

        $payment = Payment::create([
            'bill_id' => $bill->id,
            'amount_paid' => 500000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'Transfer Bank',
            'status' => 'Sukses',
        ]);

        $response = $this->actingAs($this->parentUser)
            ->get(route('ortu.pembayaran.pdf', $payment->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_parent_cannot_download_other_student_raport_or_receipt(): void
    {
        // parentUser attempts to download otherStudent's raport -> 403
        $raportResponse = $this->actingAs($this->parentUser)
            ->get(route('ortu.raport.pdf', $this->otherStudent->id));
        $raportResponse->assertStatus(403);

        // Payment for otherStudent
        $billType = BillType::create(['name' => 'Uang Buku', 'amount' => 150000, 'period' => 'Sekali Bayar']);
        $bill = Bill::create([
            'student_id' => $this->otherStudent->id,
            'bill_type_id' => $billType->id,
            'amount_due' => 150000,
            'status' => 'Lunas',
            'month' => '11',
            'year' => '2026',
        ]);

        $payment = Payment::create([
            'bill_id' => $bill->id,
            'amount_paid' => 150000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'Tunai',
            'status' => 'Sukses',
        ]);

        // parentUser attempts to download otherStudent's payment receipt -> 403
        $paymentResponse = $this->actingAs($this->parentUser)
            ->get(route('ortu.pembayaran.pdf', $payment->id));
        $paymentResponse->assertStatus(403);
    }
}
