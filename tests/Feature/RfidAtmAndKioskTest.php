<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\CanteenMenu;
use App\Models\CanteenStand;
use App\Models\KioskDevice;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RfidAtmAndKioskTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Super Admin', 'Pengelola Stand Kantin', 'Bendahara', 'Siswa', 'Orang Tua'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    /**
     * @return array{0: User, 1: Student, 2: Wallet}
     */
    private function createStudentWithRfid(string $rfid = 'RFID-1001', float $balance = 100000, string $pin = '123456', ?User $parent = null): array
    {
        $studentUser = User::factory()->create(['name' => 'Zaki Pratama']);
        $studentUser->assignRole('Siswa');

        $academicYear = AcademicYear::firstOrCreate(
            ['name' => '2026/2027', 'semester' => 'Ganjil'],
            ['is_active' => true]
        );
        $class = StudentClass::firstOrCreate(
            ['academic_year_id' => $academicYear->id, 'name' => '5A'],
            ['level' => 5]
        );

        $student = Student::create([
            'user_id' => $studentUser->id,
            'parent_user_id' => $parent?->id,
            'student_class_id' => $class->id,
            'nis' => (string) random_int(10000, 99999),
            'nisn' => (string) random_int(1000000000, 9999999999),
            'nik' => (string) random_int(3403010000000000, 3403019999999999),
            'rfid_uid' => $rfid,
        ]);

        $wallet = Wallet::create([
            'student_id' => $student->id,
            'balance' => $balance,
            'pin' => $pin,
        ]);

        return [$studentUser, $student, $wallet];
    }

    public function test_bendahara_can_link_rfid_card_and_canteen_can_check_balance_and_pay_with_rfid_pin(): void
    {
        $bendahara = User::factory()->create();
        $bendahara->assignRole('Bendahara');

        $owner = User::factory()->create();
        $owner->assignRole('Pengelola Stand Kantin');

        $stand = CanteenStand::create([
            'user_id' => $owner->id,
            'name' => 'Stand Sehat',
            'balance' => 0,
            'is_open' => true,
        ]);

        $menu = CanteenMenu::create([
            'canteen_stand_id' => $stand->id,
            'name' => 'Bubur Ayam',
            'price' => 10000,
            'stock' => 10,
            'is_available' => true,
        ]);

        [$studentUser, $student, $wallet] = $this->createStudentWithRfid(rfid: 'RFID-OLD', balance: 50000, pin: '654321');

        $this->actingAs($bendahara)
            ->post(route('bendahara.dompet.rfid', $student), ['rfid_uid' => 'RFID-998877'])
            ->assertRedirect();

        $this->assertSame('RFID-998877', $student->fresh()->rfid_uid);

        $this->actingAs($owner)
            ->postJson(route('kantin.rfid.check-balance'), ['rfid_uid' => 'RFID-998877'])
            ->assertOk()
            ->assertJsonPath('student.name', 'Zaki Pratama')
            ->assertJsonPath('wallet.balance', 50000);

        // PIN salah ditolak
        $this->actingAs($owner)
            ->post(route('kantin.rfid.pay'), [
                'rfid_uid' => 'RFID-998877',
                'pin' => '000000',
                'items' => [['menu_id' => $menu->id, 'quantity' => 1]],
            ])
            ->assertSessionHasErrors('pin');

        // PIN benar -> saldo tabungan terpotong & stok berkurang
        $this->actingAs($owner)
            ->post(route('kantin.rfid.pay'), [
                'rfid_uid' => 'RFID-998877',
                'pin' => '654321',
                'items' => [['menu_id' => $menu->id, 'quantity' => 2]],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(30000.0, (float) $wallet->fresh()->balance);
        $this->assertSame(20000.0, (float) $stand->fresh()->balance);
        $this->assertSame(8, $menu->fresh()->stock);

        // Siswa cek saldo dengan PIN
        $this->actingAs($studentUser)
            ->postJson(route('siswa.kantin.check-balance'), ['pin' => '000000'])
            ->assertStatus(422);

        $this->actingAs($studentUser)
            ->postJson(route('siswa.kantin.check-balance'), ['pin' => '654321'])
            ->assertOk()
            ->assertJsonPath('balance', 30000)
            ->assertJsonPath('rfid_uid', 'RFID-998877');
    }

    public function test_kiosk_presensi_is_locked_by_multi_mac_addresses_and_records_rfid_attendance(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');

        [$studentUser] = $this->createStudentWithRfid(rfid: 'RFID-SISWA-01', parent: $parent);

        // Tanpa MAC terdaftar -> 403
        $this->get(route('kiosk.presensi'))->assertForbidden();
        $this->withHeader('X-Device-Mac', '11:22:33:44:55:66')
            ->get(route('kiosk.presensi'))
            ->assertForbidden();

        // Admin daftarkan 2 MAC Address PC Kiosk
        $this->actingAs($admin)->post(route('sistem.kiosk-devices.store'), [
            'name' => 'Kiosk Gerbang 1',
            'mac_address' => 'aa-bb-cc-dd-ee-01',
            'location' => 'Gerbang Utama',
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('sistem.kiosk-devices.store'), [
            'name' => 'Kiosk Gerbang 2',
            'mac_address' => 'AA:BB:CC:DD:EE:02',
            'location' => 'Gerbang Timur',
        ])->assertRedirect();

        $device1 = KioskDevice::where('mac_address', 'AA:BB:CC:DD:EE:01')->firstOrFail();

        // Buka dengan MAC terdaftar ke-1 & ke-2
        $this->withHeader('X-Device-Mac', 'AA:BB:CC:DD:EE:01')
            ->get(route('kiosk.presensi'))
            ->assertOk();

        $this->withHeader('X-Device-Mac', 'AA:BB:CC:DD:EE:02')
            ->get(route('kiosk.presensi'))
            ->assertOk();

        // Tap RFID Masuk
        $this->withHeader('X-Device-Mac', 'AA:BB:CC:DD:EE:01')
            ->postJson(route('kiosk.presensi.tap'), ['rfid_uid' => 'RFID-SISWA-01'])
            ->assertOk()
            ->assertJsonPath('record.mode', 'Masuk');

        $attendance = Attendance::where('user_id', $studentUser->id)->firstOrFail();
        $this->assertNotNull($attendance->time_in);
        $this->assertNull($attendance->time_out);
        $this->assertSame(1, $parent->notifications()->count());

        // Tap RFID Pulang
        $this->withHeader('X-Device-Mac', 'AA:BB:CC:DD:EE:02')
            ->postJson(route('kiosk.presensi.tap'), ['rfid_uid' => 'RFID-SISWA-01'])
            ->assertOk()
            ->assertJsonPath('record.mode', 'Pulang');

        $this->assertNotNull($attendance->fresh()->time_out);

        // Nonaktifkan perangkat 1 -> MAC 1 langsung terkunci 403, MAC 2 tetap aktif
        $this->actingAs($admin)->patch(route('sistem.kiosk-devices.toggle', $device1))->assertRedirect();

        $this->withHeader('X-Device-Mac', 'AA:BB:CC:DD:EE:01')
            ->get(route('kiosk.presensi'))
            ->assertForbidden();

        $this->withHeader('X-Device-Mac', 'AA:BB:CC:DD:EE:02')
            ->get(route('kiosk.presensi'))
            ->assertOk();
    }
}
