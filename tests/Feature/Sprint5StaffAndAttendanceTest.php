<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Payroll;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint5StaffAndAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Super Admin', 'Bendahara', 'Staf Sekolah', 'Guru Pengajar', 'Siswa'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    public function test_staff_can_view_real_dashboard_with_actual_database_statistics(): void
    {
        $staffUser = User::factory()->create(['name' => 'Pak Joko Staf TU']);
        $staffUser->assignRole('Staf Sekolah');

        Attendance::create([
            'user_id' => $staffUser->id,
            'date' => today()->toDateString(),
            'time_in' => '07:10:00',
            'status' => 'Hadir',
        ]);

        Book::create([
            'title' => 'Kamus Besar Bahasa Indonesia',
            'author' => 'Badan Bahasa',
            'physical_stock' => 5,
        ]);

        $this->actingAs($staffUser)
            ->get(route('staf.dashboard'))
            ->assertOk();
    }

    public function test_staff_geolocation_attendance_enforces_configured_school_radius(): void
    {
        $staffUser = User::factory()->create(['name' => 'Staf Lokasi']);
        $staffUser->assignRole('Staf Sekolah');

        // Koordinat sekolah SD Muhammadiyah Al Mujahidin (-7.9664, 110.6035) radius 150m
        SystemSetting::setValue('school_latitude', '-7.9664');
        SystemSetting::setValue('school_longitude', '110.6035');
        SystemSetting::setValue('school_radius_meters', '150');

        // Lokasi di luar radius sekolah (misal di Yogyakarta kota ~ 35 km)
        $this->actingAs($staffUser)
            ->post(route('staf.absensi.store'), [
                'type' => 'check_in',
                'latitude' => -7.7956,
                'longitude' => 110.3695,
            ])
            ->assertSessionHasErrors('latitude');

        $this->assertDatabaseMissing('attendances', ['user_id' => $staffUser->id]);

        // Lokasi valid di dalam radius sekolah (-7.96641, 110.60351 ~ radius 2 meter)
        $this->actingAs($staffUser)
            ->post(route('staf.absensi.store'), [
                'type' => 'check_in',
                'latitude' => -7.96641,
                'longitude' => 110.60351,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $att = Attendance::where('user_id', $staffUser->id)->firstOrFail();
        $this->assertSame('Hadir', $att->status);
        $this->assertNotNull($att->time_in);
        $this->assertNull($att->time_out);

        // Check-out pulang
        $this->actingAs($staffUser)
            ->post(route('staf.absensi.store'), [
                'type' => 'check_out',
                'latitude' => -7.96641,
                'longitude' => 110.60351,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNotNull($att->fresh()->time_out);
    }

    public function test_staff_library_circulation_decrements_stock_on_borrow_and_increments_on_return(): void
    {
        $staffUser = User::factory()->create(['name' => 'Pustakawan']);
        $staffUser->assignRole('Staf Sekolah');

        $borrower = User::factory()->create(['name' => 'Ahmad Siswa']);
        $borrower->assignRole('Siswa');

        $book = Book::create([
            'title' => 'Ensiklopedia Sains Anak',
            'author' => 'Tim Editor',
            'physical_stock' => 3,
        ]);

        $this->actingAs($staffUser)
            ->post(route('staf.perpustakaan.borrow'), [
                'book_id' => $book->id,
                'borrower_id' => $borrower->id,
                'loan_duration_days' => 7,
                'notes' => 'Peminjaman pertama',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(2, $book->fresh()->physical_stock);

        $loan = BookLoan::where('book_id', $book->id)->firstOrFail();
        $this->assertSame('Dipinjam', $loan->status);

        // Proses pengembalian buku
        $this->actingAs($staffUser)
            ->post(route('staf.perpustakaan.return', $loan))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(3, $book->fresh()->physical_stock);
        $this->assertSame('Dikembalikan', $loan->fresh()->status);
    }

    public function test_bendahara_payroll_generation_includes_staff_attendance_and_staff_can_view_slip_gaji(): void
    {
        $bendahara = User::factory()->create();
        $bendahara->assignRole('Bendahara');

        $staffUser = User::factory()->create(['name' => 'Staf Tata Usaha']);
        $staffUser->assignRole('Staf Sekolah');

        // Catat 10 hari hadir presensi staf
        for ($i = 1; $i <= 10; $i++) {
            Attendance::create([
                'user_id' => $staffUser->id,
                'date' => Carbon::create(2026, 11, $i)->toDateString(),
                'time_in' => '07:05:00',
                'status' => 'Hadir',
            ]);
        }

        $this->actingAs($bendahara)
            ->post(route('bendahara.payroll.generate'), [
                'month' => 'November',
                'year' => '2026',
                'base_salary' => 3000000,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $payroll = Payroll::where('user_id', $staffUser->id)
            ->where('month', 'November')
            ->firstOrFail();

        // 10 hari x Rp 25.000 = Rp 250.000 tunjangan kehadiran
        $this->assertDatabaseHas('payroll_components', [
            'payroll_id' => $payroll->id,
            'name' => 'Tunjangan Kehadiran Staf (10 Hari)',
            'amount' => 250000,
        ]);

        // Staf melihat slip gaji sendiri
        $this->actingAs($staffUser)
            ->get(route('staf.gaji'))
            ->assertOk();
    }

    public function test_legacy_akademik_mock_routes_redirect_to_active_modules(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('Guru Pengajar');

        $this->actingAs($teacher)
            ->get(route('akademik.absensi'))
            ->assertRedirect(route('guru.absensi'));

        $this->actingAs($teacher)
            ->get(route('akademik.jadwal'))
            ->assertRedirect(route('guru.jadwal'));

        $this->actingAs($teacher)
            ->get(route('akademik.nilai'))
            ->assertRedirect(route('guru.nilai'));
    }
}
