<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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
            'Pengelola Stand Kantin'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
