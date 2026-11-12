<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class UsersImport implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row      = $row->toArray();

        // Ensure essential fields exist
        if (empty($row['email']) || empty($row['name'])) {
            return;
        }

        // Create or update user
        $user = User::updateOrCreate(
            ['email' => $row['email']],
            [
                'name' => $row['name'],
                'password' => isset($row['password']) ? Hash::make($row['password']) : Hash::make('password123'),
            ]
        );

        // Assign Role
        if (!empty($row['role'])) {
            $user->assignRole($row['role']);
        }

        // Handle specific logic for 'Siswa'
        if ($user->hasRole('Siswa')) {
            Student::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'student_class_id' => $row['rombel_id'] ?? null,
                    'nisn' => $row['nisn'] ?? null,
                    'nik' => $row['nik'] ?? null,
                    'nama_ayah' => $row['nama_ayah'] ?? null,
                    'nama_ibu' => $row['nama_ibu'] ?? null,
                    'pekerjaan_ayah' => $row['pekerjaan_ayah'] ?? null,
                    'pekerjaan_ibu' => $row['pekerjaan_ibu'] ?? null,
                    'no_telp_ortu' => $row['no_telp_ortu'] ?? null,
                ]
            );
        }
    }
}
