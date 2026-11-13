<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Super Admin',
            'Admin Tata Usaha',
            'Guru Pengajar',
            'Guru BK',
            'Bendahara',
            'Operator',
            'Staf Sekolah',
            'Siswa',
            'Orang Tua',
            'Calon Siswa Baru',
            'Pengelola Stand Kantin',
            'Bidang Lomba',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
