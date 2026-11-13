<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin Tata Usaha
        $adminTu = User::firstOrCreate([
            'email' => 'admin.tu@sekolah.com',
        ], [
            'name' => 'Budi Admin TU',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        if (! $adminTu->hasRole('Admin Tata Usaha')) {
            $adminTu->assignRole('Admin Tata Usaha');
        }

        // 2. Guru Pengajar
        $guru = User::firstOrCreate([
            'email' => 'guru@sekolah.com',
        ], [
            'name' => 'Siti Guru',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        if (! $guru->hasRole('Guru Pengajar')) {
            $guru->assignRole('Guru Pengajar');
        }

        // 3. Guru BK
        $guruBk = User::firstOrCreate([
            'email' => 'gurubk@sekolah.com',
        ], [
            'name' => 'Tono Guru BK',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        if (! $guruBk->hasRole('Guru BK')) {
            $guruBk->assignRole('Guru BK');
        }

        // 4. Bendahara
        $bendahara = User::firstOrCreate([
            'email' => 'bendahara@sekolah.com',
        ], [
            'name' => 'Ayu Bendahara',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        if (! $bendahara->hasRole('Bendahara')) {
            $bendahara->assignRole('Bendahara');
        }

        // 5. Operator
        $operator = User::firstOrCreate([
            'email' => 'operator@sekolah.com',
        ], [
            'name' => 'Udin Operator',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        if (! $operator->hasRole('Operator')) {
            $operator->assignRole('Operator');
        }

        // 6. Staf Sekolah
        $staf = User::firstOrCreate([
            'email' => 'staf@sekolah.com',
        ], [
            'name' => 'Joko Staf',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        if (! $staf->hasRole('Staf Sekolah')) {
            $staf->assignRole('Staf Sekolah');
        }

        // 7. Siswa
        $siswa = User::firstOrCreate([
            'email' => 'siswa@sekolah.com',
        ], [
            'name' => 'Andi Siswa',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        if (! $siswa->hasRole('Siswa')) {
            $siswa->assignRole('Siswa');
        }

        // 8. Orang Tua
        $ortu = User::firstOrCreate([
            'email' => 'ortu@sekolah.com',
        ], [
            'name' => 'Bapak Andi',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        if (! $ortu->hasRole('Orang Tua')) {
            $ortu->assignRole('Orang Tua');
        }

        // 9. Calon Siswa Baru
        $calon = User::firstOrCreate([
            'email' => 'calon@sekolah.com',
        ], [
            'name' => 'Calon Budi',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        if (! $calon->hasRole('Calon Siswa Baru')) {
            $calon->assignRole('Calon Siswa Baru');
        }

        // 10. Pengelola Stand Kantin
        $kantin = User::firstOrCreate([
            'email' => 'kantin@sekolah.com',
        ], [
            'name' => 'Ibu Kantin',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        if (! $kantin->hasRole('Pengelola Stand Kantin')) {
            $kantin->assignRole('Pengelola Stand Kantin');
        }
    }
}
