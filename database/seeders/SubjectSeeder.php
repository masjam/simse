<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            ['code' => 'MTK', 'name' => 'Matematika'],
            ['code' => 'BIN', 'name' => 'Bahasa Indonesia'],
            ['code' => 'IPAS', 'name' => 'IPAS'],
            ['code' => 'PAI', 'name' => 'Pendidikan Agama Islam'],
            ['code' => 'AIK', 'name' => 'Al-Islam dan Kemuhammadiyahan'],
            ['code' => 'BAR', 'name' => 'Bahasa Arab'],
            ['code' => 'BIG', 'name' => 'Bahasa Inggris'],
            ['code' => 'PJOK', 'name' => 'PJOK'],
            ['code' => 'SB', 'name' => 'Seni Budaya'],
            ['code' => 'THF', 'name' => 'Tahfidz'],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                ['code' => $subject['code']],
                ['name' => $subject['name']]
            );
        }
    }
}
