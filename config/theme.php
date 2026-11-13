<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Active Theme
    |--------------------------------------------------------------------------
    |
    | The default visual theme for the SIMSE application.
    | Options: 'madinah', 'madinah-gelap', 'uji-gelap'.
    |
    */
    'default' => 'madinah',

    /*
    |--------------------------------------------------------------------------
    | Role-based Default Themes
    |--------------------------------------------------------------------------
    |
    | Configured default theme per authenticated user role.
    |
    */
    'roles' => [
        'Super Admin' => 'madinah',
        'Guru Pengajar' => 'madinah',
        'Bendahara' => 'madinah',
        'Staf Sekolah' => 'madinah',
        'Siswa' => 'madinah',
        'Orang Tua' => 'madinah',
        'Pengelola Stand Kantin' => 'madinah',
    ],

    /*
    |--------------------------------------------------------------------------
    | Available Registered Themes
    |--------------------------------------------------------------------------
    */
    'available' => [
        'madinah' => [
            'name' => 'Madinah (Standar)',
            'description' => 'Nuansa hijau-teal teduh dengan aksen emas Al-Mujahidin',
            'type' => 'light',
        ],
        'madinah-gelap' => [
            'name' => 'Madinah Gelap',
            'description' => 'Mode gelap ramah mata untuk penggunaan malam hari',
            'type' => 'dark',
        ],
        'uji-gelap' => [
            'name' => 'Uji Gelap',
            'description' => 'Tema uji kontras tinggi untuk pengujian design system',
            'type' => 'dark',
        ],
    ],
];
