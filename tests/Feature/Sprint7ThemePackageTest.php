<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Module;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\ModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class Sprint7ThemePackageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        app(ModuleService::class)->ensureSeeded();
        Module::query()->update(['is_active' => true]);

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Bendahara', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Orang Tua', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Pengelola Stand Kantin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);
    }

    public function test_all_seven_themes_css_files_exist(): void
    {
        $themes = [
            'madinah',
            'taman-belajar',
            'kertas-tinta',
            'ledger-tegas',
            'senja-kaca',
            'pasar-kantin',
            'fokus-ujian',
        ];

        foreach ($themes as $theme) {
            $path = resource_path("css/themes/{$theme}.css");
            $this->assertFileExists($path, "File tema {$theme}.css harus ada di resources/css/themes/");
            $content = file_get_contents($path);
            $this->assertStringContainsString("[data-theme=\"{$theme}\"]", $content);
        }
    }

    public function test_role_default_themes_are_correctly_assigned(): void
    {
        // 1. Super Admin -> madinah
        $admin = User::factory()->create(['name' => 'Admin']);
        $admin->assignRole('Super Admin');
        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('theme', 'madinah'));

        // 2. Bendahara -> ledger-tegas
        $bendahara = User::factory()->create(['name' => 'Bendahara']);
        $bendahara->assignRole('Bendahara');
        $this->actingAs($bendahara)->get(route('bendahara.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('theme', 'ledger-tegas'));

        // 3. Orang Tua -> senja-kaca
        $academicYear = AcademicYear::firstOrCreate([
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);
        $class = StudentClass::create([
            'academic_year_id' => $academicYear->id,
            'name' => '4B',
            'level' => 4,
        ]);

        $ortu = User::factory()->create(['name' => 'Wali Siswa']);
        $ortu->assignRole('Orang Tua');
        $childUser = User::factory()->create(['name' => 'Anak Wali']);
        Student::create([
            'user_id' => $childUser->id,
            'parent_user_id' => $ortu->id,
            'student_class_id' => $class->id,
            'nisn' => '0099887766',
            'nis' => '9901',
            'nik' => '3403010101990001',
            'gender' => 'L',
        ]);
        $this->actingAs($ortu)->get(route('ortu.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('theme', 'senja-kaca'));

        // 4. Kantin -> pasar-kantin
        $kantin = User::factory()->create(['name' => 'Pengelola Stand']);
        $kantin->assignRole('Pengelola Stand Kantin');
        $this->actingAs($kantin)->get(route('kantin.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('theme', 'pasar-kantin'));
    }

    public function test_student_grades_one_to_three_receive_taman_belajar_theme(): void
    {
        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $classLevel2 = StudentClass::create([
            'academic_year_id' => $academicYear->id,
            'name' => 'Kelas 2 Bilal',
            'level' => 2,
        ]);

        $studentUser = User::factory()->create(['name' => 'Siswa Kelas 2']);
        $studentUser->assignRole('Siswa');

        Student::create([
            'user_id' => $studentUser->id,
            'student_class_id' => $classLevel2->id,
            'nisn' => '0011223344',
            'nis' => '1001',
            'nik' => '3403010101010001',
            'gender' => 'L',
        ]);

        $this->actingAs($studentUser)->get(route('siswa.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('theme', 'taman-belajar'));
    }

    public function test_user_can_switch_theme_via_cookie_when_allowed(): void
    {
        $admin = User::factory()->create(['name' => 'Admin']);
        $admin->assignRole('Super Admin');

        // Saat allow_user_switch aktif, kirim cookie simse_theme
        $response = $this->actingAs($admin)
            ->withCookie('simse_theme', 'kertas-tinta')
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->where('theme', 'kertas-tinta'));
    }

    public function test_super_admin_can_lock_theme_switching(): void
    {
        SystemSetting::setValue('theme_allow_user_switch', 'false');

        $bendahara = User::factory()->create(['name' => 'Bendahara']);
        $bendahara->assignRole('Bendahara');

        // Walau user mencoba kirim cookie tema lain, sistem tetap mengunci ke default peran (ledger-tegas)
        $response = $this->actingAs($bendahara)
            ->withCookie('simse_theme', 'pasar-kantin')
            ->get(route('bendahara.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->where('theme', 'ledger-tegas'));
    }
}
