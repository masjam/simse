<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
    canRegister: {
        type: Boolean,
        default: true,
    },
    laravelVersion: {
        type: String,
        default: '',
    },
    phpVersion: {
        type: String,
        default: '',
    },
});

const mobileMenuOpen = ref(false);
const activeRoleTab = ref('guru');
const activeFaq = ref(null);

const toggleFaq = (index) => {
    activeFaq.value = activeFaq.value === index ? null : index;
};

const roleTabs = [
    {
        id: 'guru',
        title: 'Guru & Pendidik',
        icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        description: 'Fokus mendidik tanpa terbebani administrasi manual. Semua terintegrasi dari jadwal hingga e-Raport.',
        features: [
            'Input nilai harian, PTS, dan PAS langsung export/import Excel',
            'Sistem CBT anti-curang dengan bank soal otomatis',
            'Manajemen materi LMS & pengumpulan tugas siswa interaktif',
            'Absensi kelas harian terhubung langsung ke wali murid',
            'Akses transparan slip gaji & tunjangan mengajar (Payroll)'
        ],
        previewTag: 'Portal Guru Pengajar',
        badgeColor: 'bg-indigo-100 text-indigo-700',
    },
    {
        id: 'siswa',
        title: 'Siswa & Pelajar',
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        description: 'Pengalaman belajar modern dalam genggaman. Jadwal, nilai, hingga jajan kantin secara non-tunai.',
        features: [
            'KTS Digital dengan QR Code dinamis untuk presensi & perpustakaan',
            'Ujian online CBT yang nyaman, adil, dan transparan',
            'Akses materi video & modul interaktif di portal LMS',
            'e-Kantin & dompet digital pintar dengan barcode KTS',
            'Pantau rekapitulasi poin prestasi dan catatan akademik'
        ],
        previewTag: 'Dashboard Mandiri Siswa',
        badgeColor: 'bg-emerald-100 text-emerald-700',
    },
    {
        id: 'bendahara',
        title: 'Bendahara & Kasir',
        icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
        description: 'Kelola arus kas sekolah, penagihan SPP, pos kasir belanja, dan payroll staf dengan akurasi 100%.',
        features: [
            'Monitoring status bayar SPP per tingkat dan rombel secara real-time',
            'Point of Sales (POS) Kasir transaksi pembayaran sekolah',
            'Kalkulasi otomatis gaji pokok, jam mengajar, dan potongan',
            'Kwitansi digital otomatis terkirim ke WhatsApp wali murid',
            'Laporan arus kas dan buku besar siap audit kapan saja'
        ],
        previewTag: 'Finance & Cashier Hub',
        badgeColor: 'bg-amber-100 text-amber-700',
    },
    {
        id: 'admin',
        title: 'Super Admin & TU',
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
        description: 'Kendali penuh atas seluruh modul, data induk DAPODIK, jadwal otomatis bebas bentrok, dan otoritas pengguna.',
        features: [
            'Auto-Generate Jadwal Pelajaran cerdas bebas jadwal bentrok guru',
            'Sinkronisasi & import/export data siswa, guru, dan rombel via Excel',
            'Manajemen 11 role pengguna dengan izin akses terperinci',
            'Manajemen modul aktif/nonaktif sesuai kebutuhan sekolah',
            'Cetak data siswa standar buku induk lengkap dalam 1 klik'
        ],
        previewTag: 'Master Command Center',
        badgeColor: 'bg-purple-100 text-purple-700',
    },
    {
        id: 'orangtua',
        title: 'Wali Murid',
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        description: 'Ketenangan hati orang tua dengan visibilitas penuh atas absensi, gizi kantin, serta prestasi anak.',
        features: [
            'Notifikasi kehadiran masuk dan pulang sekolah secara instan',
            'Pantau riwayat jajanan kantin & tetapkan limit jajan harian anak',
            'Akses langsung laporan rapor tengah & akhir semester',
            'Informasi tagihan SPP dan riwayat pembayaran transparan',
            'Komunikasi dua arah dengan guru BK dan wali kelas'
        ],
        previewTag: 'Parent Monitoring Portal',
        badgeColor: 'bg-blue-100 text-blue-700',
    }
];

const modulesList = [
    {
        name: 'SIAKAD & Akademik',
        desc: 'Manajemen tingkat, rombel, distribusi mata pelajaran, dan jadwal pelajaran otomatis.',
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
        color: 'from-blue-500 to-indigo-600',
        lightBg: 'bg-blue-50 text-blue-600'
    },
    {
        name: 'CBT Anti-Curang',
        desc: 'Ujian online dengan proteksi auto-submit saat siswa keluar jendela atau ganti tab.',
        icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        color: 'from-rose-500 to-red-600',
        lightBg: 'bg-rose-50 text-rose-600'
    },
    {
        name: 'LMS & Tugas Interaktif',
        desc: 'Ruang belajar digital untuk materi silabus, video instruksi, dan pengumpulan tugas terstruktur.',
        icon: 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
        color: 'from-violet-500 to-purple-600',
        lightBg: 'bg-violet-50 text-violet-600'
    },
    {
        name: 'e-Kantin & Saldo Siswa',
        desc: 'Belanja kantin cashless via barcode KTS dengan riwayat transaksi detail bagi orang tua.',
        icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        color: 'from-emerald-500 to-teal-600',
        lightBg: 'bg-emerald-50 text-emerald-600'
    },
    {
        name: 'Presensi QR & Face ID',
        desc: 'Pencatatan kehadiran siswa & guru dengan pemindaian barcode atau foto bukti lokasi akurat.',
        icon: 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z',
        color: 'from-cyan-500 to-blue-600',
        lightBg: 'bg-cyan-50 text-cyan-600'
    },
    {
        name: 'SPP & Keuangan Sekolah',
        desc: 'Tagihan iuran bulanan otomatis, kasir penerimaan pembayaran kas, dan pembukuan transparan.',
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        color: 'from-amber-500 to-orange-600',
        lightBg: 'bg-amber-50 text-amber-600'
    },
    {
        name: 'Payroll & Slip Gaji Guru',
        desc: 'Perhitungan honor jam mengajar, gaji pokok, potongan koperasi, dan generate slip gaji PDF.',
        icon: 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z',
        color: 'from-indigo-500 to-pink-600',
        lightBg: 'bg-indigo-50 text-indigo-600'
    },
    {
        name: 'Bimbingan Konseling (BK)',
        desc: 'Pencatatan sesi konseling siswa terenkripsi, buku pelanggaran bertingkat, dan rekap poin prestasi.',
        icon: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
        color: 'from-pink-500 to-rose-600',
        lightBg: 'bg-pink-50 text-pink-600'
    },
    {
        name: 'Perpustakaan Digital',
        desc: 'Katalog buku fisik dan e-book, sirkulasi peminjaman barcode, dan notifikasi keterlambatan.',
        icon: 'M8 14v20c0 4.418 7.163 8 16 8 1.38 0 2.721-.087 4-.252V22M8 14c0 4.418 7.163 8 16 8s16-3.582 16-8M8 14c0-4.418 7.163-8 16-8s16 3.582 16 8m0 0v14m0-4c0 4.418-7.163 8-16 8-1.38 0-2.721-.087-4-.252',
        color: 'from-teal-500 to-green-600',
        lightBg: 'bg-teal-50 text-teal-600'
    },
    {
        name: 'WhatsApp Gateway',
        desc: 'Pengiriman pesan instan otomatis: presensi anak, jatuh tempo SPP, dan pengumuman sekolah.',
        icon: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
        color: 'from-emerald-600 to-green-700',
        lightBg: 'bg-emerald-50 text-emerald-700'
    },
    {
        name: 'PPDB Online Terpadu',
        desc: 'Formulir pendaftaran calon siswa baru, unggah berkas KK/Akta, verifikasi kuota, dan cetak bukti daftar.',
        icon: 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
        color: 'from-sky-500 to-indigo-600',
        lightBg: 'bg-sky-50 text-sky-600'
    },
    {
        name: 'KTS Digital & Barcode',
        desc: 'Kartu Tanda Siswa ber-QR Code dengan standar identitas nasional (NISN & NIK DAPODIK).',
        icon: 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2',
        color: 'from-amber-600 to-rose-600',
        lightBg: 'bg-amber-50 text-amber-700'
    }
];

const faqs = [
    {
        q: 'Apakah SIMSE dapat disinkronkan dengan data DAPODIK?',
        a: 'Ya, SIMSE dirancang kompatibel penuh dengan skema data standar DAPODIK Kemendikbudristek. Anda dapat mengimpor dan mengekspor data siswa, guru, rombel, dan mata pelajaran dalam format template Microsoft Excel yang telah disediakan.'
    },
    {
        q: 'Bagaimana cara kerja sistem Ujian CBT Anti-Curang?',
        a: 'Sistem CBT dilengkapi dengan pelacak status layar penuh (Fullscreen Enforcer) dan deteksi pergantian jendela (Tab-Switch Detector). Jika siswa mencoba membuka peramban lain, meminimalkan aplikasi, atau berganti tab, sistem akan memberikan peringatan dan dapat disetel untuk melakukan Forced Submit secara otomatis.'
    },
    {
        q: 'Bagaimana mekanisme pembayaran di e-Kantin sekolah?',
        a: 'Setiap siswa memiliki Kartu Tanda Siswa (KTS) Digital dengan QR Code unik. Siswa cukup melakukan scan di stan kantin untuk melakukan pembayaran dari saldo dompet digital mereka. Orang tua dapat memantau riwayat jajan dan membatasi limit belanja harian anak melalui portal wali murid.'
    },
    {
        q: 'Siapa saja yang memiliki hak akses ke dalam sistem?',
        a: 'SIMSE mendukung 11 peran (Role) spesifik berbasis Spatie Permission, mencakup Super Admin, Admin Tata Usaha, Guru Pengajar, Guru BK, Bendahara, Operator, Staf Sekolah, Siswa, Orang Tua, Calon Siswa Baru, dan Pengelola Stand Kantin.'
    },
    {
        q: 'Apakah jadwal pelajaran dapat digenerate secara otomatis?',
        a: 'Tentu saja. Fitur Auto-Generate Jadwal kami menggunakan algoritma pengecekan batasan waktu dan alokasi ruang yang menjamin tidak adanya jam bentrok bagi guru pengajar di rombel manapun.'
    }
];
</script>

<template>
    <Head title="SIMSE - Sistem Informasi Manajemen Sekolah Terpadu Masa Depan" />

    <div class="min-h-screen bg-slate-900 text-slate-100 font-sans selection:bg-indigo-500 selection:text-white antialiased">
        
        <!-- TOP NOTIFICATION BANNER -->
        <div class="relative z-50 bg-gradient-to-r from-indigo-700 via-purple-700 to-pink-600 text-white text-xs sm:text-sm font-medium py-2 px-4 text-center shadow-inner flex items-center justify-center gap-2">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-white text-indigo-700 shadow-sm animate-pulse">
                BARU
            </span>
            <span>SIMSE v2026.2 Rilis: Ujian CBT Anti-Curang, e-Kantin Cashless, & Presensi QR Resmi Aktif!</span>
        </div>

        <!-- STICKY HEADER NAVBAR -->
        <header class="sticky top-0 z-40 backdrop-blur-xl bg-slate-900/80 border-b border-slate-800/80 transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    
                    <!-- BRAND LOGO -->
                    <Link href="/" class="flex items-center gap-3 group">
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-500 p-0.5 shadow-lg shadow-indigo-500/25 group-hover:scale-105 transition-transform">
                            <div class="w-full h-full bg-slate-950 rounded-[14px] flex items-center justify-center">
                                <svg class="w-6 h-6 text-indigo-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v7" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl font-black tracking-tight text-white font-mono">SIMSE</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                    PRO
                                </span>
                            </div>
                            <span class="text-[11px] text-slate-400 block -mt-1 font-medium tracking-wide">
                                Sistem Manajemen Sekolah Terpadu
                            </span>
                        </div>
                    </Link>

                    <!-- DESKTOP NAVIGATION LINKS -->
                    <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-300">
                        <a href="#fitur" class="hover:text-indigo-400 transition-colors">Fitur Unggulan</a>
                        <a href="#modul" class="hover:text-indigo-400 transition-colors">21 Modul</a>
                        <a href="#peran" class="hover:text-indigo-400 transition-colors">Portal Pengguna</a>
                        <a href="#keamanan" class="hover:text-indigo-400 transition-colors">Keamanan</a>
                        <a href="#faq" class="hover:text-indigo-400 transition-colors">Tanya Jawab</a>
                    </nav>

                    <!-- ACTION BUTTONS -->
                    <div class="hidden sm:flex items-center gap-3">
                        <template v-if="$page.props.auth && $page.props.auth.user">
                            <Link 
                                :href="route('dashboard')" 
                                class="inline-flex items-center gap-2 text-sm font-semibold text-white px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 shadow-lg shadow-indigo-600/30 transition-all hover:shadow-indigo-600/50"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                Masuk Dashboard
                            </Link>
                        </template>
                        <template v-else>
                            <Link 
                                v-if="canLogin" 
                                :href="route('login')" 
                                class="text-sm font-semibold text-slate-200 hover:text-white px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-colors"
                            >
                                Masuk (Login)
                            </Link>
                            <Link 
                                v-if="canRegister" 
                                :href="route('register')" 
                                class="inline-flex items-center gap-2 text-sm font-semibold text-white px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 shadow-lg shadow-indigo-600/30 transition-all hover:scale-105 active:scale-95"
                            >
                                <span>Pendaftaran PPDB</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </Link>
                        </template>
                    </div>

                    <!-- MOBILE MENU TOGGLE -->
                    <button 
                        @click="mobileMenuOpen = !mobileMenuOpen" 
                        type="button" 
                        class="md:hidden p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                        aria-label="Buka menu navigasi"
                    >
                        <svg v-if="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- MOBILE NAV POPUP -->
            <div v-show="mobileMenuOpen" class="md:hidden border-b border-slate-800 bg-slate-900/95 backdrop-blur-2xl px-4 pt-3 pb-6 space-y-3">
                <a @click="mobileMenuOpen = false" href="#fitur" class="block px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800 font-medium">Fitur Unggulan</a>
                <a @click="mobileMenuOpen = false" href="#modul" class="block px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800 font-medium">21 Modul Terpadu</a>
                <a @click="mobileMenuOpen = false" href="#peran" class="block px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800 font-medium">Portal Multi-Role</a>
                <a @click="mobileMenuOpen = false" href="#keamanan" class="block px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800 font-medium">Keamanan & DAPODIK</a>
                <a @click="mobileMenuOpen = false" href="#faq" class="block px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800 font-medium">Tanya Jawab</a>
                
                <div class="pt-4 border-t border-slate-800 flex flex-col gap-2">
                    <template v-if="$page.props.auth && $page.props.auth.user">
                        <Link :href="route('dashboard')" class="text-center w-full py-2.5 rounded-xl bg-indigo-600 text-white font-semibold shadow-md">
                            Buka Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link v-if="canLogin" :href="route('login')" class="text-center w-full py-2.5 rounded-xl bg-slate-800 text-slate-200 font-semibold border border-slate-700">
                            Masuk (Login)
                        </Link>
                        <Link v-if="canRegister" :href="route('register')" class="text-center w-full py-2.5 rounded-xl bg-indigo-600 text-white font-semibold shadow-md">
                            Daftar PPDB Baru
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- HERO SECTION -->
        <section class="relative pt-12 pb-24 lg:pt-20 lg:pb-36 overflow-hidden">
            <!-- BACKGROUND GLOWS & GRID -->
            <div class="absolute inset-0 pointer-events-none z-0">
                <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[700px] lg:w-[1100px] h-[550px] bg-gradient-to-tr from-indigo-600/25 via-purple-600/20 to-pink-500/10 rounded-full blur-[128px] opacity-70"></div>
                <div class="absolute top-1/2 -left-32 w-80 h-80 bg-blue-600/15 rounded-full blur-[100px]"></div>
                <div class="absolute bottom-10 -right-20 w-80 h-80 bg-purple-600/15 rounded-full blur-[100px]"></div>
                <!-- Subtle grid overlay -->
                <div class="absolute inset-0 bg-[linear-gradient(to_right,#1e293b0a_1px,transparent_1px),linear-gradient(to_bottom,#1e293b0a_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                
                <!-- PILL BADGE -->
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 text-xs sm:text-sm font-semibold mb-8 backdrop-blur-md shadow-lg shadow-indigo-500/10 animate-fade-in">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>Sistem Manajemen Sekolah Cerdas Generasi 2026</span>
                    <span class="text-slate-400">|</span>
                    <span class="text-slate-300 font-normal">Tahun Ajaran 2026/2027</span>
                </div>

                <!-- MAIN HEADLINE -->
                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight text-white max-w-5xl mx-auto leading-[1.15] mb-8">
                    Satu Ekosistem Cerdas Untuk Seluruh 
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-purple-300 to-pink-400">
                        Aktivitas Sekolah Anda
                    </span>
                </h1>

                <!-- SUBTITLE -->
                <p class="max-w-3xl mx-auto text-base sm:text-xl text-slate-300 mb-12 font-normal leading-relaxed">
                    Tinggalkan sistem terpisah yang rumit. SIMSE memadukan <strong>SIAKAD</strong>, 
                    <strong>Ujian Online (CBT) Anti-Curang</strong>, <strong>e-Kantin Non-Tunai</strong>, 
                    <strong>Presensi QR & Face ID</strong>, hingga <strong>Notifikasi WhatsApp Otomatis</strong> dalam satu portal modern.
                </p>

                <!-- CALL TO ACTIONS -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-16">
                    <Link 
                        v-if="canLogin" 
                        :href="route('login')" 
                        class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 text-white font-bold text-base shadow-xl shadow-indigo-600/35 hover:shadow-indigo-600/60 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-3"
                    >
                        <span>Akses Portal Sekarang</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </Link>
                    
                    <a 
                        href="#modul" 
                        class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-slate-800/80 hover:bg-slate-750 text-slate-200 font-semibold text-base border border-slate-700/80 backdrop-blur-md shadow-md hover:border-slate-600 transition-all flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        <span>Eksplorasi 21 Modul</span>
                    </a>
                </div>

                <!-- LIVE METRIC SUMMARY CARDS -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-5xl mx-auto text-left">
                    <div class="p-5 rounded-2xl bg-slate-800/50 border border-slate-700/60 backdrop-blur-xl relative overflow-hidden group hover:border-indigo-500/40 transition-colors">
                        <div class="text-2xl sm:text-3xl font-extrabold text-white font-mono">21+</div>
                        <div class="text-xs sm:text-sm font-semibold text-indigo-400 mt-1">Modul Terintegrasi</div>
                        <p class="text-slate-400 text-xs mt-1">Dari Kurikulum hingga e-Kantin</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-800/50 border border-slate-700/60 backdrop-blur-xl relative overflow-hidden group hover:border-emerald-500/40 transition-colors">
                        <div class="text-2xl sm:text-3xl font-extrabold text-emerald-400 font-mono">100%</div>
                        <div class="text-xs sm:text-sm font-semibold text-emerald-400 mt-1">CBT Anti-Curang</div>
                        <p class="text-slate-400 text-xs mt-1">Auto-submit saat keluar tab</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-800/50 border border-slate-700/60 backdrop-blur-xl relative overflow-hidden group hover:border-purple-500/40 transition-colors">
                        <div class="text-2xl sm:text-3xl font-extrabold text-purple-400 font-mono">11 Role</div>
                        <div class="text-xs sm:text-sm font-semibold text-purple-400 mt-1">Hak Akses Fleksibel</div>
                        <p class="text-slate-400 text-xs mt-1">Guru, Staf, Siswa, Wali Murid</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-800/50 border border-slate-700/60 backdrop-blur-xl relative overflow-hidden group hover:border-pink-500/40 transition-colors">
                        <div class="text-2xl sm:text-3xl font-extrabold text-pink-400 font-mono">Real-Time</div>
                        <div class="text-xs sm:text-sm font-semibold text-pink-400 mt-1">WhatsApp Notifikasi</div>
                        <p class="text-slate-400 text-xs mt-1">Absensi & tagihan langsung ke HP</p>
                    </div>
                </div>

                <!-- PREVIEW DASHBOARD MOCKUP -->
                <div class="mt-16 max-w-6xl mx-auto rounded-3xl p-2 bg-gradient-to-b from-slate-700/50 via-slate-800/30 to-slate-900 border border-slate-700/60 shadow-2xl shadow-indigo-950/70">
                    <div class="rounded-2xl bg-slate-950 overflow-hidden border border-slate-800">
                        <!-- MOCKUP BROWSER HEADER -->
                        <div class="bg-slate-900/90 px-4 py-3 border-b border-slate-800 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-rose-500/80"></div>
                                <div class="w-3 h-3 rounded-full bg-amber-500/80"></div>
                                <div class="w-3 h-3 rounded-full bg-emerald-500/80"></div>
                                <span class="ml-2 text-xs font-mono text-slate-400 hidden sm:inline">simse.sekolah.sch.id/dashboard</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-mono text-[10px]">● SISTEM ONLINE</span>
                            </div>
                        </div>

                        <!-- MOCKUP CONTENT -->
                        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
                            <div class="bg-slate-900 p-5 rounded-2xl border border-slate-800">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Presensi Hari Ini</span>
                                    <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-md">98.6% Hadir</span>
                                </div>
                                <div class="text-3xl font-black text-white font-mono">1,385 <span class="text-sm font-normal text-slate-400">/ 1,405 Siswa</span></div>
                                <div class="w-full bg-slate-800 h-2 rounded-full mt-3 overflow-hidden">
                                    <div class="bg-emerald-500 h-full rounded-full" style="width: 98.6%"></div>
                                </div>
                                <p class="text-xs text-slate-400 mt-3">Presensi QR Gate otomatis merekap waktu kehadiran siswa.</p>
                            </div>

                            <div class="bg-slate-900 p-5 rounded-2xl border border-slate-800">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">CBT Anti-Curang</span>
                                    <span class="text-xs font-bold text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded-md">12 Sesi Aktif</span>
                                </div>
                                <div class="text-3xl font-black text-white font-mono">380 <span class="text-sm font-normal text-slate-400">Siswa Mengerjakan</span></div>
                                <div class="flex items-center gap-2 mt-3 text-xs text-indigo-300 font-medium">
                                    <span class="w-2 h-2 rounded-full bg-indigo-400 animate-ping"></span>
                                    <span>Zero Tab-Switch Violation terdeteksi</span>
                                </div>
                                <p class="text-xs text-slate-400 mt-2">Deteksi otomatis jendela keluar & peringatan suara.</p>
                            </div>

                            <div class="bg-slate-900 p-5 rounded-2xl border border-slate-800">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Transaksi e-Kantin</span>
                                    <span class="text-xs font-bold text-purple-400 bg-purple-500/10 px-2 py-0.5 rounded-md">Cashless</span>
                                </div>
                                <div class="text-3xl font-black text-white font-mono">Rp 5.820.000</div>
                                <div class="flex items-center justify-between mt-3 text-xs text-slate-400">
                                    <span>542 Transaksi</span>
                                    <span class="text-emerald-400 font-semibold">+18% dari kemarin</span>
                                </div>
                                <p class="text-xs text-slate-400 mt-2">Siswa membayar makanan menggunakan KTS Digital.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- 3 CORE PILLARS SECTION -->
        <section id="fitur" class="py-24 bg-slate-950 relative border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="text-xs font-bold tracking-widest text-indigo-400 uppercase bg-indigo-500/10 px-3 py-1 rounded-full border border-indigo-500/20">
                        Inovasi Terdepan
                    </span>
                    <h2 class="text-3xl sm:text-5xl font-black text-white mt-4 tracking-tight">
                        Dirancang Khusus Menjawab Masalah Nyata Sekolah
                    </h2>
                    <p class="mt-4 text-slate-400 text-base sm:text-lg">
                        Bukan sekadar software pendataan, melainkan asisten digital yang melindungi integritas ujian dan merapikan sirkulasi keuangan sekolah.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    
                    <!-- PILLAR 1 -->
                    <div class="rounded-3xl bg-slate-900/70 border border-slate-800 p-8 relative overflow-hidden group hover:border-indigo-500/50 hover:bg-slate-900 transition-all duration-300">
                        <div class="w-16 h-16 rounded-2xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-3">Ujian CBT Anti-Curang</h3>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6">
                            Sistem secara aktif memantau fokus browser siswa. Fitur <strong>Forced Submit</strong> akan menutup ujian dan mencatat pelanggaran bila siswa sengaja membuka tab lain, browser ganda, atau keluar dari mode fullscreen.
                        </p>
                        <ul class="space-y-2 text-xs text-slate-300 border-t border-slate-800/80 pt-4">
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span>Pencegahan Split Screen & Alt-Tab</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span>Acak Urutan Soal & Pilihan Jawaban</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span>Koreksi otomatis instan untuk soal objektif</span>
                            </li>
                        </ul>
                    </div>

                    <!-- PILLAR 2 -->
                    <div class="rounded-3xl bg-slate-900/70 border border-slate-800 p-8 relative overflow-hidden group hover:border-emerald-500/50 hover:bg-slate-900 transition-all duration-300">
                        <div class="w-16 h-16 rounded-2xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-emerald-600 group-hover:text-white transition-all">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-3">e-Kantin & Dompet KTS</h3>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6">
                            Ubah seluruh kantin sekolah menjadi ekosistem digital non-tunai. Siswa cukup menggunakan KTS Digital. Menghindari uang hilang dan memberikan kontrol belanja gizi bagi wali murid.
                        </p>
                        <ul class="space-y-2 text-xs text-slate-300 border-t border-slate-800/80 pt-4">
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span>Bayar cepat dengan barcode KTS Digital</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span>Pengaturan batas maksimal jajan harian</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span>Rekap omzet otomatis untuk mitra kantin</span>
                            </li>
                        </ul>
                    </div>

                    <!-- PILLAR 3 -->
                    <div class="rounded-3xl bg-slate-900/70 border border-slate-800 p-8 relative overflow-hidden group hover:border-purple-500/50 hover:bg-slate-900 transition-all duration-300">
                        <div class="w-16 h-16 rounded-2xl bg-purple-500/10 text-purple-400 border border-purple-500/20 flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-purple-600 group-hover:text-white transition-all">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-3">Portal Orang Tua & WA Gate</h3>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6">
                            Jembatan komunikasi sekolah dan wali murid tanpa hambatan. Presensi kehadiran anak masuk/pulang dikirimkan detik itu juga langsung ke aplikasi WhatsApp orang tua.
                        </p>
                        <ul class="space-y-2 text-xs text-slate-300 border-t border-slate-800/80 pt-4">
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span>Notifikasi WA presensi tepat waktu</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span>Kwitansi pembayaran SPP digital</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span>Pantau perkembangan nilai & konseling BK</span>
                            </li>
                        </ul>
                    </div>

                </div>

            </div>
        </section>

        <!-- 21 INTEGRATED MODULES SHOWCASE -->
        <section id="modul" class="py-24 bg-slate-900 relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
                    <div>
                        <span class="text-xs font-bold tracking-widest text-indigo-400 uppercase bg-indigo-500/10 px-3 py-1 rounded-full border border-indigo-500/20">
                            Arsitektur Sistem
                        </span>
                        <h2 class="text-3xl sm:text-5xl font-black text-white mt-4 tracking-tight">
                            Ekosistem Terpadu 21 Modul
                        </h2>
                        <p class="mt-3 text-slate-400 max-w-2xl text-base">
                            Semua fitur yang dibutuhkan sekolah mulai dari akademik, keuangan, kepegawaian, hingga layanan siswa bekerja selaras dalam satu basis data.
                        </p>
                    </div>
                    <div class="text-left md:text-right">
                        <span class="text-sm font-semibold text-slate-400">Total Modul Aktif:</span>
                        <div class="text-2xl font-black text-indigo-400 font-mono">21 / 21 Online</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div 
                        v-for="(mod, index) in modulesList" 
                        :key="index"
                        class="p-6 rounded-2xl bg-slate-950/60 border border-slate-800 hover:border-slate-700 hover:bg-slate-950 transition-all duration-200 group flex flex-col justify-between"
                    >
                        <div>
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4 transition-transform group-hover:scale-105" :class="mod.lightBg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="mod.icon" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-white mb-2 group-hover:text-indigo-300 transition-colors">
                                {{ mod.name }}
                            </h4>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                {{ mod.desc }}
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-850 flex items-center justify-between text-[11px] text-slate-500">
                            <span>Status: Aktif</span>
                            <span class="text-emerald-400 flex items-center gap-1 font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Siap Pakai
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- MULTI-ROLE PORTAL TABBED SHOWCASE -->
        <section id="peran" class="py-24 bg-slate-950 relative border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <span class="text-xs font-bold tracking-widest text-indigo-400 uppercase bg-indigo-500/10 px-3 py-1 rounded-full border border-indigo-500/20">
                        Akses Berbasis Peran
                    </span>
                    <h2 class="text-3xl sm:text-5xl font-black text-white mt-4 tracking-tight">
                        Tampilan & Fungsi Berbeda untuk Tiap Pengguna
                    </h2>
                    <p class="mt-4 text-slate-400 text-base sm:text-lg">
                        Masing-masing pengguna mendapatkan portal khusus yang dirancang sesuai alur kerja harian mereka.
                    </p>
                </div>

                <!-- TABS HEADER -->
                <div class="flex flex-wrap items-center justify-center gap-2 mb-10">
                    <button 
                        v-for="tab in roleTabs" 
                        :key="tab.id"
                        @click="activeRoleTab = tab.id"
                        class="px-5 py-3 rounded-2xl text-sm font-semibold transition-all flex items-center gap-2"
                        :class="activeRoleTab === tab.id 
                            ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 scale-105' 
                            : 'bg-slate-900 text-slate-400 hover:text-slate-200 hover:bg-slate-850 border border-slate-800'"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tab.icon" />
                        </svg>
                        <span>{{ tab.title }}</span>
                    </button>
                </div>

                <!-- TAB CONTENT PANEL -->
                <div 
                    v-for="tab in roleTabs" 
                    :key="tab.id"
                    v-show="activeRoleTab === tab.id"
                    class="rounded-3xl bg-slate-900/80 border border-slate-800 p-8 lg:p-12 transition-all duration-300"
                >
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                        <div class="lg:col-span-7">
                            <div class="inline-block px-3 py-1 rounded-full text-xs font-bold mb-4" :class="tab.badgeColor">
                                {{ tab.previewTag }}
                            </div>
                            <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight mb-4">
                                {{ tab.title }}
                            </h3>
                            <p class="text-slate-300 text-base leading-relaxed mb-6">
                                {{ tab.description }}
                            </p>
                            
                            <div class="space-y-3">
                                <div 
                                    v-for="(feature, fIndex) in tab.features" 
                                    :key="fIndex"
                                    class="flex items-start gap-3 text-sm text-slate-200"
                                >
                                    <div class="mt-1 w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                    <span>{{ feature }}</span>
                                </div>
                            </div>

                            <div class="mt-8 flex gap-4">
                                <Link 
                                    v-if="canLogin"
                                    :href="route('login')" 
                                    class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-md transition-all inline-flex items-center gap-2"
                                >
                                    <span>Masuk ke Portal {{ tab.title }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                </Link>
                            </div>
                        </div>

                        <!-- GRAPHICAL PREVIEW BOX -->
                        <div class="lg:col-span-5">
                            <div class="rounded-2xl bg-slate-950 p-6 border border-slate-800 shadow-xl">
                                <div class="flex items-center justify-between pb-4 border-b border-slate-850 mb-4">
                                    <span class="text-xs font-mono text-slate-400">Hak Akses: {{ tab.title }}</span>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 font-semibold">Terenkripsi</span>
                                </div>
                                <div class="space-y-3 text-xs">
                                    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                                        <span class="text-slate-300 font-medium">Autentikasi Aman</span>
                                        <span class="text-emerald-400 font-mono">Breeze & Sanctum</span>
                                    </div>
                                    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                                        <span class="text-slate-300 font-medium">Sistem Hak Akses</span>
                                        <span class="text-indigo-400 font-mono">Spatie Permission</span>
                                    </div>
                                    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                                        <span class="text-slate-300 font-medium">Protokol Frontend</span>
                                        <span class="text-purple-400 font-mono">Inertia.js v2 SPA</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- SECURITY & COMPLIANCE SECTION -->
        <section id="keamanan" class="py-24 bg-slate-900 relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <span class="text-xs font-bold tracking-widest text-indigo-400 uppercase bg-indigo-500/10 px-3 py-1 rounded-full border border-indigo-500/20">
                            Keandalan & Standar
                        </span>
                        <h2 class="text-3xl sm:text-5xl font-black text-white mt-4 tracking-tight leading-tight">
                            Keamanan Tingkat Tinggi & Standar Data DAPODIK
                        </h2>
                        <p class="mt-4 text-slate-300 text-base leading-relaxed">
                            SIMSE dibangun di atas framework Laravel 13 dan arsitektur modern yang menjamin kecepatan, kestabilan, serta perlindungan data pribadi siswa dan riwayat keuangan sekolah.
                        </p>

                        <div class="mt-8 space-y-4">
                            <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-950/50 border border-slate-800">
                                <div class="p-2 rounded-xl bg-indigo-500/10 text-indigo-400 shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                </div>
                                <div>
                                    <h4 class="text-base font-bold text-white">Role-Based Access Control (RBAC)</h4>
                                    <p class="text-xs text-slate-400 mt-1">Setiap pengguna hanya dapat melihat dan mengubah modul yang didelegasikan padanya.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-950/50 border border-slate-800">
                                <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                </div>
                                <div>
                                    <h4 class="text-base font-bold text-white">Import & Export Data Excel DAPODIK</h4>
                                    <p class="text-xs text-slate-400 mt-1">Mendukung sinkronisasi massal data siswa, nomor induk, NISN, data periodik, dan riwayat prestasi.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-950/50 border border-slate-800">
                                <div class="p-2 rounded-xl bg-purple-500/10 text-purple-400 shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                </div>
                                <div>
                                    <h4 class="text-base font-bold text-white">Responsif & Mobile PWA Ready</h4>
                                    <p class="text-xs text-slate-400 mt-1">Dapat diakses lancar melalui laptop guru, smartphone siswa, tablet kasir, hingga layar scanner pos satpam.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TECH SPEC PILLARS -->
                    <div class="rounded-3xl bg-slate-950 p-8 border border-slate-800 shadow-2xl">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            Spesifikasi Mesin & Pondasi Teknologi
                        </h3>

                        <div class="space-y-4">
                            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800/80">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-semibold text-white">Backend Engine</span>
                                    <span class="text-xs font-mono text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded">Laravel 13 & PHP 8.3</span>
                                </div>
                                <p class="text-xs text-slate-400">Kecepatan pemrosesan tinggi, migrasi basis data transaksional, & orkestrasi queue.</p>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800/80">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-semibold text-white">Frontend Architecture</span>
                                    <span class="text-xs font-mono text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded">Vue 3 + Inertia.js 2.0</span>
                                </div>
                                <p class="text-xs text-slate-400">Pengalaman SPA mulus tanpa reload halaman, reactive forms, & instant routing.</p>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800/80">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-semibold text-white">Design & Layouting</span>
                                    <span class="text-xs font-mono text-cyan-400 bg-cyan-500/10 px-2 py-0.5 rounded">Tailwind CSS 3</span>
                                </div>
                                <p class="text-xs text-slate-400">Desain antarmuka bersih, tema gelap modern, dan responsif di setiap ukuran layar.</p>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800/80">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-semibold text-white">Barcode & KTS Generator</span>
                                    <span class="text-xs font-mono text-pink-400 bg-pink-500/10 px-2 py-0.5 rounded">QR Code Vue 3</span>
                                </div>
                                <p class="text-xs text-slate-400">Generasi QR instan sisi klien tanpa beban server tambahan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ ACCORDION SECTION -->
        <section id="faq" class="py-24 bg-slate-950 relative border-t border-slate-800">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <span class="text-xs font-bold tracking-widest text-indigo-400 uppercase bg-indigo-500/10 px-3 py-1 rounded-full border border-indigo-500/20">
                        Pertanyaan Umum
                    </span>
                    <h2 class="text-3xl sm:text-5xl font-black text-white mt-4 tracking-tight">
                        Pertanyaan Seputar SIMSE
                    </h2>
                    <p class="mt-4 text-slate-400 text-base">
                        Temukan jawaban cepat atas pertanyaan yang sering diajukan mengenai implementasi sistem.
                    </p>
                </div>

                <div class="space-y-4">
                    <div 
                        v-for="(faq, index) in faqs" 
                        :key="index"
                        class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden transition-all"
                    >
                        <button 
                            @click="toggleFaq(index)"
                            type="button"
                            class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 text-slate-200 hover:text-white font-bold text-base"
                        >
                            <span>{{ faq.q }}</span>
                            <svg 
                                class="w-5 h-5 text-indigo-400 transform transition-transform duration-200 shrink-0" 
                                :class="{ 'rotate-180': activeFaq === index }"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div 
                            v-show="activeFaq === index" 
                            class="px-6 pb-5 pt-1 text-sm text-slate-300 leading-relaxed border-t border-slate-800/60"
                        >
                            {{ faq.a }}
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- BOTTOM CALL TO ACTION BANNER -->
        <section class="py-20 relative overflow-hidden bg-gradient-to-br from-indigo-900 via-slate-900 to-purple-950 border-t border-slate-800 text-center">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-indigo-500/10 via-transparent to-transparent pointer-events-none"></div>

            <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <span class="px-4 py-1.5 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-bold uppercase tracking-wider border border-indigo-500/30">
                    Siap Memulai Transformasi?
                </span>
                
                <h2 class="text-3xl sm:text-5xl font-black text-white mt-6 mb-6 tracking-tight">
                    Wujudkan Manajemen Sekolah Yang Tertib, Modern, & Transparan
                </h2>
                
                <p class="max-w-2xl mx-auto text-slate-300 text-base sm:text-lg mb-10 leading-relaxed">
                    Masuk ke portal dengan akun terdaftar Anda atau daftarkan calon peserta didik baru melalui sistem seleksi PPDB Online kami.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <Link 
                        v-if="canLogin" 
                        :href="route('login')" 
                        class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-white text-slate-950 font-bold text-base shadow-xl hover:bg-slate-100 hover:scale-105 active:scale-95 transition-all flex items-center justify-center gap-2"
                    >
                        <span>Masuk ke Akun Anda</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </Link>

                    <Link 
                        v-if="canRegister" 
                        :href="route('register')" 
                        class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-base shadow-lg shadow-indigo-600/40 hover:scale-105 active:scale-95 transition-all flex items-center justify-center gap-2"
                    >
                        <span>Daftar Siswa Baru (PPDB)</span>
                    </Link>
                </div>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="bg-slate-950 text-slate-400 py-16 border-t border-slate-850">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-12">
                    
                    <!-- BRAND COL -->
                    <div class="md:col-span-2">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-md">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6"></path></svg>
                            </div>
                            <span class="text-2xl font-black text-white font-mono tracking-tight">SIMSE</span>
                        </div>
                        <p class="text-sm text-slate-400 max-w-md leading-relaxed">
                            Sistem Informasi Manajemen Sekolah Terpadu. Mengintegrasikan seluruh siklus akademik, administrasi guru, tata usaha, perpustakaan, dompet digital, hingga komunikasi orang tua secara realtime.
                        </p>
                        <div class="mt-4 text-xs text-slate-500 font-mono">
                            Versi Aplikasi: 2026.2.0 • Status Server: Online
                        </div>
                    </div>

                    <!-- QUICK LINKS -->
                    <div>
                        <h4 class="text-white font-bold text-sm mb-4 uppercase tracking-wider">Modul Utama</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#fitur" class="hover:text-white transition-colors">SIAKAD & Rombel</a></li>
                            <li><a href="#fitur" class="hover:text-white transition-colors">CBT Anti-Curang</a></li>
                            <li><a href="#fitur" class="hover:text-white transition-colors">LMS & Tugas Siswa</a></li>
                            <li><a href="#fitur" class="hover:text-white transition-colors">e-Kantin & Dompet KTS</a></li>
                            <li><a href="#fitur" class="hover:text-white transition-colors">SPP & Kasir Sekolah</a></li>
                            <li><a href="#fitur" class="hover:text-white transition-colors">Bimbingan Konseling (BK)</a></li>
                        </ul>
                    </div>

                    <!-- PORTAL ROLES -->
                    <div>
                        <h4 class="text-white font-bold text-sm mb-4 uppercase tracking-wider">Pintu Masuk Portal</h4>
                        <ul class="space-y-2 text-sm">
                            <li><Link :href="route('login')" class="hover:text-white transition-colors">Portal Super Admin</Link></li>
                            <li><Link :href="route('login')" class="hover:text-white transition-colors">Portal Guru Pengajar</Link></li>
                            <li><Link :href="route('login')" class="hover:text-white transition-colors">Portal Bendahara & Kasir</Link></li>
                            <li><Link :href="route('login')" class="hover:text-white transition-colors">Portal Staf & Perpustakaan</Link></li>
                            <li><Link :href="route('login')" class="hover:text-white transition-colors">Portal Siswa & Orang Tua</Link></li>
                            <li><Link v-if="canRegister" :href="route('register')" class="text-indigo-400 hover:text-indigo-300 transition-colors font-semibold">Pendaftaran PPDB Baru →</Link></li>
                        </ul>
                    </div>

                </div>

                <div class="pt-8 border-t border-slate-850 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                    <p>© 2026 SIMSE - Sistem Informasi Manajemen Sekolah Terpadu. Seluruh hak cipta dilindungi.</p>
                    <div class="flex items-center gap-6">
                        <span>Didukung oleh Laravel 13 & Inertia.js</span>
                        <span>Kebijakan Privasi</span>
                        <span>Ketentuan Layanan</span>
                    </div>
                </div>
            </div>
        </footer>

    </div>
</template>
