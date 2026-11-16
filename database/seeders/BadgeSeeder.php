<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $defaultBadges = [
            [
                'code' => 'first_step',
                'name' => 'Langkah Pertama',
                'description' => 'Mengumpulkan tugas pembelajaran perdana di SIMSE.',
                'icon' => '🎯',
                'category' => 'Akademik',
                'points_required' => 0,
                'threshold_count' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'score_master',
                'name' => 'Kolektor Nilai A',
                'description' => 'Meraih nilai 90 ke atas pada tugas atau kuis.',
                'icon' => '💎',
                'category' => 'Akademik',
                'points_required' => 0,
                'threshold_count' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'cbt_champion',
                'name' => 'Pendekar CBT',
                'description' => 'Menyelesaikan ujian daring CBT dengan tertib dan berintegritas.',
                'icon' => '⚡',
                'category' => 'CBT',
                'points_required' => 0,
                'threshold_count' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'diligent_student',
                'name' => 'Santri Tekun',
                'description' => 'Menyelesaikan minimal 3 penugasan pembelajaran.',
                'icon' => '🔥',
                'category' => 'Akademik',
                'points_required' => 150,
                'threshold_count' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'book_explorer',
                'name' => 'Penjelajah Pustaka',
                'description' => 'Meminjam buku atau aktif membaca literatur di perpustakaan sekolah.',
                'icon' => '📖',
                'category' => 'Literasi',
                'points_required' => 50,
                'threshold_count' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'character_role_model',
                'name' => 'Teladan Akhlak',
                'description' => 'Mendapatkan apresiasi atau bimbingan karakter positif dari guru.',
                'icon' => '🌟',
                'category' => 'Karakter',
                'points_required' => 100,
                'threshold_count' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'mujahidin_star',
                'name' => 'Bintang Al-Mujahidin',
                'description' => 'Mengumpulkan lebih dari 500 Poin Prestasi membanggakan.',
                'icon' => '👑',
                'category' => 'Prestasi',
                'points_required' => 500,
                'threshold_count' => 500,
                'is_active' => true,
            ],
        ];

        foreach ($defaultBadges as $badge) {
            Badge::updateOrCreate(
                ['code' => $badge['code']],
                $badge
            );
        }
    }
}
