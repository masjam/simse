<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Bill;
use App\Models\BillType;
use App\Models\CounselingSession;
use App\Models\Grade;
use App\Models\ParentOtp;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentViolation;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use App\Models\TeachingSchedule;
use App\Models\Tingkat;
use App\Models\User;
use App\Models\ViolationPoint;
use App\Models\WhatsappLog;
use App\Services\WhatsApp\FonnteWhatsAppGateway;
use App\Services\WhatsApp\WablasWhatsAppGateway;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ParentPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $parentUser;

    private Student $childOne;

    private Student $childTwo;

    private Student $otherChild;

    private Teacher $teacher;

    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Orang Tua', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Guru Pengajar', 'guard_name' => 'web']);

        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $tingkat4 = Tingkat::create(['name' => 'Kelas 4']);
        $tingkat1 = Tingkat::create(['name' => 'Kelas 1']);

        $teacherUser = User::factory()->create(['name' => 'Ustadzah Siti']);
        $teacherUser->assignRole('Guru Pengajar');

        $this->teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'nip' => '198407152010012015',
        ]);

        $class4A = StudentClass::create([
            'name' => '4A',
            'academic_year_id' => $academicYear->id,
            'tingkat_id' => $tingkat4->id,
            'teacher_id' => $this->teacher->id,
            'level' => '4',
        ]);

        $class1A = StudentClass::create([
            'name' => '1A',
            'academic_year_id' => $academicYear->id,
            'tingkat_id' => $tingkat1->id,
            'teacher_id' => $this->teacher->id,
            'level' => '1',
        ]);

        $this->parentUser = User::factory()->create([
            'name' => 'Bapak Andi',
            'email' => 'ortu@sekolah.com',
            'phone' => '6281234567890',
        ]);
        $this->parentUser->assignRole('Orang Tua');

        $childOneUser = User::factory()->create(['name' => 'Andi Pratama']);
        $childOneUser->assignRole('Siswa');
        $this->childOne = Student::create([
            'user_id' => $childOneUser->id,
            'parent_user_id' => $this->parentUser->id,
            'student_class_id' => $class4A->id,
            'nisn' => '0152026001',
            'nik' => '3403011505160001',
            'gender' => 'L',
            'tanggal_lahir' => '2016-05-15',
            'no_telp_ortu' => '081234567890',
            'nama_ayah' => 'Bapak Andi',
        ]);

        $childTwoUser = User::factory()->create(['name' => 'Aisyah Putri']);
        $childTwoUser->assignRole('Siswa');
        $this->childTwo = Student::create([
            'user_id' => $childTwoUser->id,
            'parent_user_id' => $this->parentUser->id,
            'student_class_id' => $class1A->id,
            'nisn' => '0192026002',
            'nik' => '3403012008190002',
            'gender' => 'P',
            'tanggal_lahir' => '2019-08-20',
            'no_telp_ortu' => '081234567890',
            'nama_ayah' => 'Bapak Andi',
        ]);

        $otherParentUser = User::factory()->create([
            'name' => 'Ibu Budi',
            'phone' => '6289876543210',
        ]);
        $otherParentUser->assignRole('Orang Tua');

        $otherChildUser = User::factory()->create(['name' => 'Budi Santoso']);
        $otherChildUser->assignRole('Siswa');
        $this->otherChild = Student::create([
            'user_id' => $otherChildUser->id,
            'parent_user_id' => $otherParentUser->id,
            'student_class_id' => $class4A->id,
            'nisn' => '0152026099',
            'nik' => '3403011001160099',
            'gender' => 'L',
            'tanggal_lahir' => '2016-01-10',
            'no_telp_ortu' => '089876543210',
        ]);

        $this->subject = Subject::create([
            'code' => 'PAI',
            'name' => 'Pendidikan Agama Islam',
        ]);
    }

    public function test_parent_otp_login_page_can_be_rendered(): void
    {
        $this->get('/ortu/otp')
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Auth/Login')
                ->where('initialRole', 'ortu')
            );
    }

    public function test_parent_can_request_and_verify_otp_without_password_and_links_siblings(): void
    {
        $this->childTwo->update(['parent_user_id' => null]);

        $this->post('/ortu/otp/request', [
            'phone_number' => '081234567890',
            'birth_date' => '2016-05-15',
        ])->assertRedirect();

        $otp = ParentOtp::where('phone_number', '6281234567890')->first();
        $this->assertNotNull($otp);

        $waLog = WhatsappLog::where('phone_number', '6281234567890')->latest()->first();
        $this->assertNotNull($waLog);
        $this->assertSame('Terkirim', $waLog->status);

        preg_match('/\*(\d{6})\*/', $waLog->message, $matches);
        $plainOtp = $matches[1] ?? '';
        $this->assertSame(6, strlen($plainOtp));

        $this->post('/ortu/otp/verify', [
            'phone_number' => '081234567890',
            'otp_code' => $plainOtp,
        ])->assertRedirect(route('ortu.dashboard', absolute: false));

        $this->assertAuthenticatedAs($this->parentUser);
        $this->assertSame($this->parentUser->id, $this->childTwo->fresh()->parent_user_id);
    }

    public function test_parent_otp_request_fails_when_birth_date_does_not_match(): void
    {
        $this->post('/ortu/otp/request', [
            'phone_number' => '081234567890',
            'birth_date' => '2010-01-01',
        ])->assertSessionHasErrors('phone_number');

        $this->assertDatabaseCount('parent_otps', 0);
    }

    public function test_parent_otp_request_is_rate_limited_after_three_attempts(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->post('/ortu/otp/request', [
                'phone_number' => '081234567890',
                'birth_date' => '2016-05-15',
            ])->assertSessionHasNoErrors();
        }

        $this->post('/ortu/otp/request', [
            'phone_number' => '081234567890',
            'birth_date' => '2016-05-15',
        ])->assertSessionHasErrors('phone_number');
    }

    public function test_parent_otp_locks_after_five_failed_verification_attempts(): void
    {
        $otp = ParentOtp::create([
            'phone_number' => '6281234567890',
            'student_id' => $this->childOne->id,
            'user_id' => $this->parentUser->id,
            'code' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(5),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/ortu/otp/verify', [
                'phone_number' => '081234567890',
                'otp_code' => '999999',
            ])->assertSessionHasErrors('otp_code');
        }

        $otp->refresh();
        $this->assertNotNull($otp->locked_until);
        $this->assertTrue($otp->locked_until->isFuture());

        $this->post('/ortu/otp/verify', [
            'phone_number' => '081234567890',
            'otp_code' => '123456',
        ])->assertSessionHasErrors('otp_code');

        $this->assertGuest();

        $this->post('/ortu/otp/request', [
            'phone_number' => '081234567890',
            'birth_date' => '2016-05-15',
        ])->assertSessionHasErrors('phone_number');
    }

    public function test_expired_otp_cannot_be_verified(): void
    {
        ParentOtp::create([
            'phone_number' => '6281234567890',
            'student_id' => $this->childOne->id,
            'user_id' => $this->parentUser->id,
            'code' => Hash::make('654321'),
            'attempts' => 0,
            'expires_at' => now()->subMinute(),
        ]);

        $this->post('/ortu/otp/verify', [
            'phone_number' => '081234567890',
            'otp_code' => '654321',
        ])->assertSessionHasErrors('otp_code');

        $this->assertGuest();
    }

    public function test_fonnte_and_wablas_drivers_send_expected_payloads_and_log(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true, 'detail' => 'sent'], 200),
            'https://solo.wablas.com/api/send-message' => Http::response(['status' => true, 'message' => 'sent'], 200),
        ]);

        $fonnte = new FonnteWhatsAppGateway('secret-fonnte-token');
        $fonnteResult = $fonnte->send('081234567890', 'Halo dari Fonnte', 'Bapak Andi');

        $this->assertTrue($fonnteResult['success']);
        $this->assertSame('fonnte', $fonnteResult['driver']);

        $wablas = new WablasWhatsAppGateway('secret-wablas-token');
        $wablasResult = $wablas->send('081234567890', 'Halo dari Wablas', 'Bapak Andi');

        $this->assertTrue($wablasResult['success']);
        $this->assertSame('wablas', $wablasResult['driver']);

        $this->assertDatabaseCount('whatsapp_logs', 2);
    }

    public function test_parent_dashboard_displays_four_tabs_data_and_supports_switching_children(): void
    {
        Grade::create([
            'student_id' => $this->childOne->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'type' => 'Tugas',
            'name' => 'Tugas Hafalan',
            'score' => 92,
        ]);

        Grade::create([
            'student_id' => $this->childTwo->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'type' => 'Tugas',
            'name' => 'Tugas Menulis Huruf Hijaiyah',
            'score' => 98,
        ]);

        TeachingSchedule::create([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'student_class_id' => $this->childOne->student_class_id,
            'day' => 1,
            'time_start' => '07:15:00',
            'time_end' => '08:45:00',
            'room' => 'Ruang 4A',
            'jpl' => 2,
        ]);

        TeachingJournal::create([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'student_class_id' => $this->childOne->student_class_id,
            'date' => Carbon::today()->toDateString(),
            'meeting_number' => 1,
            'topic' => 'Akhlak Terpuji',
            'summary' => 'Mempelajari sikap jujur dan amanah.',
        ]);

        Attendance::create([
            'user_id' => $this->childOne->user_id,
            'date' => Carbon::today()->toDateString(),
            'status' => 'Hadir',
            'time_in' => '06:50:00',
        ]);

        $billType = BillType::create([
            'name' => 'SPP Bulanan',
            'amount' => 350000,
            'period' => 'Bulanan',
        ]);

        Bill::create([
            'student_id' => $this->childOne->id,
            'bill_type_id' => $billType->id,
            'month' => 'November',
            'year' => '2026',
            'amount_due' => 350000,
            'status' => 'Belum Lunas',
        ]);

        $paidBill = Bill::create([
            'student_id' => $this->childOne->id,
            'bill_type_id' => $billType->id,
            'month' => 'Oktober',
            'year' => '2026',
            'amount_due' => 350000,
            'status' => 'Lunas',
        ]);

        Payment::create([
            'bill_id' => $paidBill->id,
            'user_id' => $this->parentUser->id,
            'amount_paid' => 350000,
            'payment_method' => 'Transfer Bank',
            'payment_date' => Carbon::yesterday()->toDateString(),
            'status' => 'Berhasil',
        ]);

        $violationPoint = ViolationPoint::create([
            'name' => 'Tidak Membawa Buku',
            'points' => 10,
            'category' => 'Ringan',
        ]);

        StudentViolation::create([
            'student_id' => $this->childOne->id,
            'violation_point_id' => $violationPoint->id,
            'teacher_id' => $this->teacher->id,
            'date' => Carbon::yesterday()->toDateString(),
            'description' => 'Lupa membawa buku cetak.',
            'status' => 'Tercatat',
        ]);

        CounselingSession::create([
            'student_id' => $this->childOne->id,
            'teacher_id' => $this->teacher->id,
            'type' => 'Pribadi',
            'date' => Carbon::today()->toDateString(),
            'problem' => 'Sering lupa jadwal membawa perlengkapan.',
            'solution' => 'Membuat jadwal cek tas malam hari.',
            'status' => 'Selesai',
        ]);

        $this->actingAs($this->parentUser)
            ->get(route('ortu.dashboard', ['child' => $this->childOne->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Parent/Dashboard')
                ->has('children', 2)
                ->where('activeChild.id', $this->childOne->id)
                ->has('akademik.raportSubjects', 1)
                ->has('akademik.schedules', 1)
                ->has('akademik.weeklyJournals', 1)
                ->where('presensi.summary.hadir', 1)
                ->where('keuangan.total_due_unpaid', 350000)
                ->where('keuangan.total_paid_verified', 350000)
                ->has('keuangan.bills', 2)
                ->has('keuangan.payments', 1)
                ->where('perilaku.total_points', 10)
                ->has('perilaku.violations', 1)
                ->has('perilaku.counseling_sessions', 1)
            );

        $this->actingAs($this->parentUser)
            ->post(route('ortu.switch-child'), [
                'student_id' => $this->childTwo->id,
            ])
            ->assertRedirect(route('ortu.dashboard'));

        $this->actingAs($this->parentUser)
            ->get(route('ortu.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->where('activeChild.id', $this->childTwo->id)
                ->where('activeChild.name', 'Aisyah Putri')
                ->where('overview.average_score', 98)
            );
    }

    public function test_parent_cannot_access_unlinked_child_and_non_parent_cannot_access_portal(): void
    {
        $this->actingAs($this->parentUser)
            ->get(route('ortu.dashboard', ['child' => $this->otherChild->id]))
            ->assertForbidden();

        $this->actingAs($this->parentUser)
            ->post(route('ortu.switch-child'), [
                'student_id' => $this->otherChild->id,
            ])
            ->assertForbidden();

        $studentUser = User::findOrFail($this->childOne->user_id);

        $this->actingAs($studentUser)
            ->get(route('ortu.dashboard'))
            ->assertForbidden();
    }
}
