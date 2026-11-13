<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Panggil RoleSeeder untuk membuat 11 hak akses pengguna
        $this->call(RoleSeeder::class);

        // Buat Super Admin pertama
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@sekolah.com',
        ]);
        $superAdmin->assignRole('Super Admin');

        // Panggil TeacherDemoSeeder untuk mengisi data demo UI Guru
        $this->call(TeacherDemoSeeder::class);
    }
}
