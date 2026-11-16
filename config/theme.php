<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Active Theme
    |--------------------------------------------------------------------------
    */
    'default' => 'madinah',

    /*
    |--------------------------------------------------------------------------
    | Role & Context Default Themes
    |--------------------------------------------------------------------------
    |
    | Pemetaan tema standar berdasarkan peran pengguna dan konteks halaman:
    | - Super Admin, Guru -> madinah
    | - Guru (e-Raport, Perpustakaan) -> kertas-tinta
    | - Bendahara -> ledger-tegas
    | - Ortu -> senja-kaca
    | - Siswa tingkat 1-3 -> taman-belajar
    | - Siswa tingkat 4-6 -> madinah
    | - Kantin -> pasar-kantin
    | - Ujian CBT -> fokus-ujian
    |
    */
    'roles' => [
        'Super Admin' => 'madinah',
        'Operator' => 'madinah',
        'Admin Tata Usaha' => 'madinah',
        'Guru Pengajar' => 'madinah',
        'Guru BK' => 'madinah',
        'Bendahara' => 'ledger-tegas',
        'Staf Sekolah' => 'madinah',
        'Orang Tua' => 'senja-kaca',
        'Pengelola Stand Kantin' => 'pasar-kantin',
        'Calon Siswa Baru' => 'senja-kaca',
        'Siswa' => 'madinah', // default siswa, dinamis ke taman-belajar jika tingkat 1-3
    ],

    /*
    |--------------------------------------------------------------------------
    | Context / Route Specific Themes
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'guru.raport' => 'kertas-tinta',
        'siswa.perpustakaan' => 'kertas-tinta',
        'staf.perpustakaan' => 'kertas-tinta',
        'siswa.cbt.exam' => 'fokus-ujian',
        'bendahara.kasir' => 'ledger-tegas',
        'bendahara.payroll' => 'ledger-tegas',
        'kantin.dashboard' => 'pasar-kantin',
        'kantin.scan-qr' => 'pasar-kantin',
        'kantin.rfid-pos' => 'pasar-kantin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pengaturan Kebijakan Tema Pengguna
    |--------------------------------------------------------------------------
    |
    | Jika false, pengguna biasa tidak diizinkan mengubah tema dan tema akan
    | selalu terkunci pada tema standar yang ditentukan oleh Super Admin.
    |
    */
    'allow_user_switch' => true,

    /*
    |--------------------------------------------------------------------------
    | Available Registered Themes (9 Paket Tema)
    |--------------------------------------------------------------------------
    */
    'available' => [
        'madinah' => [
            'id' => 'madinah',
            'name' => 'Madinah (Standar)',
            'description' => 'Nuansa hijau-teal teduh dengan aksen emas Al-Mujahidin',
            'type' => 'light',
            'preview_color' => '#0F6B5C',
            'font' => 'Fraunces / Figtree',
        ],
        'taman-belajar' => [
            'id' => 'taman-belajar',
            'name' => 'Taman Belajar',
            'description' => 'Ceria, hangat, dan ramah seperti buku cerita bergambar (Siswa Kelas 1-3)',
            'type' => 'light',
            'preview_color' => '#1D6FD8',
            'font' => 'Fredoka / Figtree',
        ],
        'kertas-tinta' => [
            'id' => 'kertas-tinta',
            'name' => 'Kertas dan Tinta',
            'description' => 'Editorial klasik bernuansa kertas buku catatan dan perpustakaan',
            'type' => 'light',
            'preview_color' => '#B3361B',
            'font' => 'Newsreader / Figtree',
        ],
        'ledger-tegas' => [
            'id' => 'ledger-tegas',
            'name' => 'Ledger Tegas',
            'description' => 'Presisi akuntansi dengan garis tegas dan blok angka monospaced',
            'type' => 'light',
            'preview_color' => '#1B3FD1',
            'font' => 'Bricolage Grotesque / IBM Plex Mono',
        ],
        'senja-kaca' => [
            'id' => 'senja-kaca',
            'name' => 'Senja Kaca',
            'description' => 'Gradien lavender-persik yang lembut, modern, dan hangat bagi keluarga',
            'type' => 'light',
            'preview_color' => '#6D4AD6',
            'font' => 'Plus Jakarta Sans / Figtree',
        ],
        'pasar-kantin' => [
            'id' => 'pasar-kantin',
            'name' => 'Pasar Kantin',
            'description' => 'Hangat dan menggugah selera selayaknya papan menu kuliner sehat',
            'type' => 'light',
            'preview_color' => '#C2410C',
            'font' => 'Baloo 2 / Figtree',
        ],
        'fokus-ujian' => [
            'id' => 'fokus-ujian',
            'name' => 'Fokus Ujian',
            'description' => 'Minimalis dan tenang tanpa distraksi visual untuk ujian CBT',
            'type' => 'light',
            'preview_color' => '#27272A',
            'font' => 'Figtree',
        ],
        'madinah-gelap' => [
            'id' => 'madinah-gelap',
            'name' => 'Madinah Gelap',
            'description' => 'Mode malam nyaman untuk penggunaan di ruangan minim cahaya',
            'type' => 'dark',
            'preview_color' => '#14B8A6',
            'font' => 'Figtree',
        ],
        'uji-gelap' => [
            'id' => 'uji-gelap',
            'name' => 'Uji Gelap (Kontras Tinggi)',
            'description' => 'Tema uji kontras maksimal untuk verifikasi design system',
            'type' => 'dark',
            'preview_color' => '#2DD4BF',
            'font' => 'Figtree',
        ],
    ],
];
