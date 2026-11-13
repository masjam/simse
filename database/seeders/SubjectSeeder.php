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
            ['code' => 'PAI', 'name' => 'Pendidikan Agama Islam dan Budi Pekerti'],
            ['code' => 'PAK', 'name' => 'Pendidikan Agama Kristen dan Budi Pekerti'],
            ['code' => 'PP', 'name' => 'Pendidikan Pancasila'],
            ['code' => 'BIN', 'name' => 'Bahasa Indonesia'],
            ['code' => 'MTK', 'name' => 'Matematika'],
            ['code' => 'IPAS', 'name' => 'Ilmu Pengetahuan Alam dan Sosial (IPAS)'],
            ['code' => 'PJOK', 'name' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan'],
            ['code' => 'SBDP', 'name' => 'Seni dan Budaya'],
            ['code' => 'BIG', 'name' => 'Bahasa Inggris'],
            ['code' => 'MULOK', 'name' => 'Muatan Lokal'],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                ['code' => $subject['code']],
                ['name' => $subject['name']]
            );
        }
    }
}
