<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles & Permissions (11 roles)
        $this->call(RoleSeeder::class);

        // 2. Akun Super Admin Utama
        $superAdmin = User::firstOrCreate([
            'email' => 'admin@sdmujahidin.sch.id',
        ], [
            'name' => 'Super Admin Al Mujahidin',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        if (! $superAdmin->hasRole('Super Admin')) {
            $superAdmin->assignRole('Super Admin');
        }

        // 3. Akun Pengguna Bawaan Seluruh Role
        $this->call(UserRoleSeeder::class);

        // 4. Mata Pelajaran Resmi SD Muhammadiyah Al Mujahidin
        $this->call(SubjectSeeder::class);

        // 5. Data Struktur SD: Tingkat 1-6, Rombel 1A-6B, Guru & Jadwal
        $this->call(TeacherDemoSeeder::class);

        // 6. Data Portal Siswa (Tugas, CBT, Presensi, Nilai, KTS)
        $this->call(StudentPortalDemoSeeder::class);

        // 7. Data Portal Orang Tua (Multi-anak, Tagihan, Prestasi)
        $this->call(ParentPortalDemoSeeder::class);

        // 8. Data E-Kantin Digital (Stand, Menu Sehat, Saldo)
        $this->call(CanteenDemoSeeder::class);
    }
}
