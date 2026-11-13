<?php

namespace Tests\Feature;

use App\Contracts\WhatsAppGatewayInterface;
use App\Jobs\SendWhatsAppNotificationJob;
use App\Models\AcademicYear;
use App\Models\Bill;
use App\Models\BillType;
use App\Models\Module;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\WhatsappLog;
use App\Notifications\SimseNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class Sprint3NotificationModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Super Admin', 'Guru Pengajar', 'Bendahara', 'Siswa', 'Orang Tua'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    public function test_teacher_attendance_absence_queues_whatsapp_and_sends_in_app_notification_to_parent(): void
    {
        Queue::fake();

        $teacher = User::factory()->create();
        $teacher->assignRole('Guru Pengajar');

        $parent = User::factory()->create(['phone' => '081234567899']);
        $parent->assignRole('Orang Tua');

        $studentUser = User::factory()->create(['name' => 'Ahmad Fauzan']);
        $studentUser->assignRole('Siswa');

        $academicYear = AcademicYear::firstOrCreate(
            ['name' => '2026/2027', 'semester' => 'Ganjil'],
            ['is_active' => true]
        );
        $class = StudentClass::create([
            'academic_year_id' => $academicYear->id,
            'name' => '1A',
            'level' => 1,
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'parent_user_id' => $parent->id,
            'student_class_id' => $class->id,
            'nis' => '1001',
            'nisn' => '001001',
            'nik' => '3403010101180001',
            'no_telp_ortu' => '081234567899',
        ]);

        $response = $this->actingAs($teacher)->post(route('guru.absensi.store'), [
            'class_name' => '1A',
            'date' => '2026-11-12',
            'students' => [
                [
                    'id' => $student->id,
                    'status' => 'Sakit',
                    'leave_reason' => 'Demam',
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Queue::assertPushed(SendWhatsAppNotificationJob::class, 1);

        $this->assertDatabaseHas('whatsapp_logs', [
            'phone_number' => '6281234567899',
            'status' => 'Pending',
        ]);

        $this->assertSame(1, $parent->notifications()->count());
        $this->assertSame('Ketidakhadiran Ahmad Fauzan', $parent->notifications()->first()->data['title']);
    }

    public function test_bendahara_arrears_reminder_queues_whatsapp_and_notifies_parent(): void
    {
        Queue::fake();

        $bendahara = User::factory()->create();
        $bendahara->assignRole('Bendahara');

        $parent = User::factory()->create(['phone' => '081299988877']);
        $parent->assignRole('Orang Tua');

        $studentUser = User::factory()->create(['name' => 'Siti Aisyah']);
        $studentUser->assignRole('Siswa');

        $academicYear = AcademicYear::firstOrCreate(
            ['name' => '2026/2027', 'semester' => 'Ganjil'],
            ['is_active' => true]
        );
        $class = StudentClass::create([
            'academic_year_id' => $academicYear->id,
            'name' => '2A',
            'level' => 2,
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'parent_user_id' => $parent->id,
            'student_class_id' => $class->id,
            'nis' => '1002',
            'nisn' => '001002',
            'nik' => '3403010101180002',
            'nama_ayah' => 'Bapak Budi',
            'no_telp_ortu' => '081299988877',
        ]);

        $billType = BillType::create([
            'name' => 'SPP Bulanan',
            'amount' => 250000,
            'period' => 'Bulanan',
        ]);

        Bill::create([
            'student_id' => $student->id,
            'bill_type_id' => $billType->id,
            'month' => 11,
            'year' => 2026,
            'amount_due' => 250000,
            'status' => 'Belum Lunas',
        ]);

        $response = $this->actingAs($bendahara)->post(route('bendahara.spp.remind'), [
            'student_id' => $student->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Queue::assertPushed(SendWhatsAppNotificationJob::class, 1);

        $this->assertDatabaseHas('whatsapp_logs', [
            'phone_number' => '6281299988877',
            'status' => 'Pending',
        ]);

        $this->assertSame(1, $parent->notifications()->count());
        $this->assertSame('Pengingat Tagihan SPP Siti Aisyah', $parent->notifications()->first()->data['title']);
    }

    public function test_send_whatsapp_notification_job_updates_log_status_and_supports_retry(): void
    {
        $log = WhatsappLog::create([
            'recipient_name' => 'Wali Murid',
            'phone_number' => '081234567890',
            'message' => 'Pesan uji coba',
            'status' => 'Pending',
        ]);

        SystemSetting::setValue('wa_driver', 'fonnte');
        SystemSetting::setValue('wa_fonnte_token', 'token-rahasia');
        SystemSetting::setValue('wa_fonnte_endpoint', 'https://api.fonnte.com/send');

        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        $job = new SendWhatsAppNotificationJob($log->id);
        $job->handle(app(WhatsAppGatewayInterface::class));

        $this->assertSame('Terkirim', $log->fresh()->status);

        $job->failed(new RuntimeException('Timeout'));
        $this->assertSame('Gagal', $log->fresh()->status);

        Queue::fake();
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $this->actingAs($admin)
            ->post(route('sistem.wa.retry', $log))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('Pending', $log->fresh()->status);
        Queue::assertPushed(SendWhatsAppNotificationJob::class, 1);
    }

    public function test_super_admin_can_configure_wa_gateway_and_broadcast_announcement(): void
    {
        Queue::fake();

        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $parent = User::factory()->create(['phone' => '081311122233']);
        $parent->assignRole('Orang Tua');

        $teacher = User::factory()->create(['phone' => '081344455566']);
        $teacher->assignRole('Guru Pengajar');

        $this->actingAs($teacher)
            ->get(route('sistem.wa'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('sistem.wa.update'), [
                'wa_driver' => 'fonnte',
                'wa_is_active' => true,
                'wa_fonnte_token' => 'secret-token',
                'wa_fonnte_endpoint' => 'https://api.fonnte.com/send',
                'wa_wablas_token' => '',
                'wa_wablas_endpoint' => 'https://solo.wablas.com/api/send-message',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('fonnte', SystemSetting::getValue('wa_driver'));

        $this->actingAs($admin)
            ->post(route('sistem.wa.announcement'), [
                'target_role' => 'Orang Tua',
                'title' => 'Libur Awal Ramadhan',
                'message' => 'Besok kegiatan belajar diliburkan.',
                'send_whatsapp' => true,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        Queue::assertPushed(SendWhatsAppNotificationJob::class, 1);
        $this->assertSame(1, $parent->notifications()->count());
        $this->assertSame(0, $teacher->notifications()->count());
    }

    public function test_super_admin_can_toggle_module_and_disabled_module_blocks_routes(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $studentUser = User::factory()->create();
        $studentUser->assignRole('Siswa');

        $academicYear = AcademicYear::firstOrCreate(
            ['name' => '2026/2027', 'semester' => 'Ganjil'],
            ['is_active' => true]
        );
        $class = StudentClass::create([
            'academic_year_id' => $academicYear->id,
            'name' => '3A',
            'level' => 3,
        ]);
        Student::create([
            'user_id' => $studentUser->id,
            'student_class_id' => $class->id,
            'nis' => '1003',
            'nisn' => '001003',
            'nik' => '3403010101180003',
        ]);

        $this->actingAs($admin)
            ->post(route('sistem.pengaturan.update'), [
                'school_name' => 'SD Muhammadiyah Al Mujahidin Wonosari',
                'school_address' => 'Jl. Brigjen Katamso Wonosari',
                'school_phone' => '0274123456',
                'school_email' => 'info@sdmuhwonosari.sch.id',
                'active_semester' => 'Ganjil 2026/2027',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('Ganjil 2026/2027', SystemSetting::getValue('active_semester'));

        $this->actingAs($studentUser)->get(route('siswa.cbt'))->assertOk();

        $cbtModule = Module::where('code', 'cbt')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('sistem.modules.toggle', $cbtModule))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertFalse($cbtModule->fresh()->is_active);

        $this->actingAs($studentUser)->get(route('siswa.cbt'))->assertForbidden();
    }

    public function test_user_can_poll_and_mark_own_notifications_read(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $user->notify(new SimseNotification('Judul 1', 'Pesan 1', 'info', '/dashboard'));
        $otherUser->notify(new SimseNotification('Judul 2', 'Pesan 2', 'info', '/dashboard'));

        $notification = $user->notifications()->firstOrFail();
        $otherNotification = $otherUser->notifications()->firstOrFail();

        $this->actingAs($user)
            ->getJson(route('notifications.poll'))
            ->assertOk()
            ->assertJsonPath('unread_count', 1);

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk();

        $this->actingAs($user)
            ->post(route('notifications.read', $otherNotification->id))
            ->assertNotFound();

        $this->actingAs($user)
            ->post(route('notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);

        $user->notify(new SimseNotification('Judul 3', 'Pesan 3', 'info', '/dashboard'));

        $this->actingAs($user)
            ->post(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }
}
