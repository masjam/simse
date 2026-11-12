<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
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
    instagramPosts: {
        type: Array,
        default: () => [],
    },
});

const mobileMenuOpen = ref(false);

// Program Unggulan Otentik SD Muhammadiyah Al Mujahidin Wonosari
const programs = [
    {
        title: 'Belia Mendunia (International Student Exchange)',
        category: 'Wawasan Global',
        desc: 'Program pertukaran pelajar internasional (International Asian & Community Program) ke Singapura & Malaysia untuk membekali ananda dengan wawasan global, kepercayaan diri, dan kecakapan komunikasi internasional sejak usia dini.',
        features: [
            'Pertukaran Pelajar Asia & Komunitas Global',
            'English Immersion & Cultural Exchange',
            'Pelepasan Resmi Pemkab Gunungkidul',
            'Kemandirian & Global Mindset Sejak Belia'
        ],
        icon: 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        color: 'from-blue-600 to-teal-600',
        badge: 'Belia Mendunia',
    },
    {
        title: 'Tahfidzul Qur’an & Al Mujahidin Award',
        category: 'Spiritual & Al-Qur’an',
        desc: 'Pembinaan hafalan Al-Qur’an intensif dengan target tuntas Juz 30 dan 29 melalui metode Tartil dan Tilawati. Diakhiri dengan Munaqasyah terbuka serta prosesi Wisuda Akbar Tahfidz bertabur penghargaan siswa berprestasi.',
        features: [
            'Bimbingan Tahsin & Tajwid Tilawati',
            'Target Hafalan Juz 30 & 29 Terukur',
            'Munaqasyah Terbuka Bersama Wali Santri',
            'Penganugerahan Al Mujahidin Award'
        ],
        icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        color: 'from-emerald-500 to-teal-600',
        badge: 'Qur’ani Unggul',
    },
    {
        title: 'Multiple Intelligences & Sekolah Ramah Bakat',
        category: 'Akademik & Karakter',
        desc: 'Memadukan kurikulum nasional dan nilai Al-Islam secara integratif. Menghargai setiap kecerdasan majemuk anak dengan pendekatan pembatasan gawai (non-gadget focus) di sekolah agar anak fokus bersosialisasi dan bereksplorasi.',
        features: [
            'Pembelajaran Berbasis Proyek Ramah Anak',
            'Lingkungan Terarah Tanpa Gangguan Gawai',
            'Pengembangan Minat Sains, Robotika & TIK',
            'Klub Olimpiade Sains & Matematika Nasional'
        ],
        icon: 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        color: 'from-amber-500 to-yellow-600',
        badge: 'Ramah Bakat',
    },
    {
        title: 'Kader Pemimpin: Polcil, HW & Tapak Suci',
        category: 'Kepemimpinan & Kepanduan',
        desc: 'Mencetak kader persyarikatan dan bangsa yang tangguh, berdisiplin tinggi, dan berbudi luhur melalui Polisi Cilik (Polcil SDAM), Kepanduan Hizbul Wathan (HW), Drumband Gita Mujahidin, serta Tapak Suci Putera Muhammadiyah.',
        features: [
            'Polisi Cilik (Polcil SDAM) Juara Daerah',
            'Kemah Ceria Kepanduan Hizbul Wathan (HW)',
            'Pendekar Cilik Tapak Suci Berprestasi',
            'Drumband Gita Mujahidin Kebanggaan'
        ],
        icon: 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
        color: 'from-purple-500 to-pink-600',
        badge: 'Karakter Tangguh',
    },
];

// Fasilitas SD Muhammadiyah Al Mujahidin Wonosari
const facilities = [
    {
        name: 'Masjid Al Mujahidin Kampus Gadungsari',
        category: 'Ibadah & Karakter',
        desc: 'Pusat pembiasaan sholat dhuha, sholat dhuhur berjamaah, dan tadarus pagi. Bersih, sejuk ber-AC, berkarpet empuk, dan ramah untuk ananda berlatih ibadah tepat waktu.',
        img: 'https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?auto=format&fit=crop&w=800&q=80',
        spec: 'Kapasitas 800 Jamaah • Area Wudhu Khusus • Ber-AC Penuh',
    },
    {
        name: 'Ruang Kelas Multimedia Ber-AC & Smart Board',
        category: 'Ruang Belajar',
        desc: 'Ruang kelas kondusif dengan tata meja kursi ergonomis ramah anak, proyektor / smart screen interaktif, dan sirkulasi udara optimal penunjang full day school.',
        img: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=800&q=80',
        spec: 'Full AC • Interactive Screen • Loker Pribadi Siswa',
    },
    {
        name: 'Perpustakaan Digital & Ruang Baca Lesehan',
        category: 'Pusat Literasi',
        desc: 'Koleksi ribuan buku fabel akhlak, ensiklopedia anak, buku cerita sains, dan area baca lesehan nyaman untuk menumbuhkan kegemaran membaca sejak dini.',
        img: 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=800&q=80',
        spec: '6.500+ Koleksi • Karpet Edukatif • e-Katalog Digital',
    },
    {
        name: 'Laboratorium Komputer & CBT Center',
        category: 'Teknologi & ANBK',
        desc: 'Sarana pengenalan komputer dasar, logika koding anak, dan simulasi Asesmen Nasional Berbasis Komputer (ANBK) dengan jaringan aman berfilter internet sehat.',
        img: 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=800&q=80',
        spec: '60 Unit PC • LAN Gigabit • Filter Konten Aman',
    },
    {
        name: 'Lapangan Serbaguna & Arena Apel Gadungsari',
        category: 'Olahraga & Upacara',
        desc: 'Halaman terbuka luas dan representatif untuk upacara bendera, apel pagi kepanduan HW, parade drumband Gita Mujahidin, latihan Tapak Suci, dan olahraga.',
        img: 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=800&q=80',
        spec: 'Lantai Luas • Lapangan Futsal & Basket • Tribun Mini',
    },
    {
        name: 'Kantin Sehat Halalan Thayyiban (Cashless SIMSE)',
        category: 'Gizi & Nutrisi',
        desc: 'Menyediakan asupan makanan higienis bebas pengawet berbahaya dan bebas MSG berlebih. Terintegrasi kartu digital KTS SIMSE untuk memantau jajan sehat ananda.',
        img: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80',
        spec: '100% Halal Thayyib • Sistem Non-Tunai • Menu Bergizi',
    },
];

// Berita Terkini Nyata SD Muhammadiyah Al Mujahidin
const newsList = [
    {
        id: 1,
        title: 'Program "Belia Mendunia": Siswa SD Muhammadiyah Al Mujahidin Resmi Dilepas Pemkab ke Singapura & Malaysia',
        category: 'Pertukaran Pelajar Internasional',
        date: '28 Oktober 2026',
        readTime: '3 Menit Baca',
        excerpt: 'Wakil Bupati Gunungkidul secara resmi melepas keberangkatan delegasi ananda SD Muhammadiyah Al Mujahidin dalam agenda International Asian & Community Program.',
        img: 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=800&q=80',
        badgeColor: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    },
    {
        id: 2,
        title: 'Wisuda Akbar Tahfidzul Qur’an & Penganugerahan SD Muhammadiyah Al Mujahidin Award 2026',
        category: 'Prestasi & Keagamaan',
        date: '22 Oktober 2026',
        readTime: '4 Menit Baca',
        excerpt: 'Ratusan siswa-siswi sukses menuntaskan hafalan Juz 30 dan 29 dengan predikat Mumtaz. Disertai pemberian apresiasi istimewa bagi para teladan akhlak dan sains.',
        img: 'https://images.unsplash.com/photo-1609599006353-e629aaabfeae?auto=format&fit=crop&w=800&q=80',
        badgeColor: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
    },
    {
        id: 3,
        title: 'Polisi Cilik (Polcil) SDAM Tampil Memukau pada Peringatan Hari Lalu Lintas Bhayangkara',
        category: 'Karakter & Disiplin',
        date: '16 Oktober 2026',
        readTime: '3 Menit Baca',
        excerpt: 'Kekompakan baris-berbaris dan ketegasan formasi ananda Polcil SDAM menuai decak kagum jajaran Forkopimda dan kepolisian di alun-alun Wonosari.',
        img: 'https://images.unsplash.com/photo-1517649763962-0c623266ddc0?auto=format&fit=crop&w=800&q=80',
        badgeColor: 'bg-teal-500/10 text-teal-400 border-teal-500/20',
    },
];

// Fallback 4 Postingan Instagram Khas @sdmujahidinwnsofficial
const defaultInstagramPosts = [
    {
        id: 1,
        img: 'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=600&q=80',
        caption: 'Bismillah, melangkah dengan percaya diri membawa nama harum Gunungkidul dan Indonesia! Momen delegasi siswa SD Muhammadiyah Al Mujahidin Wonosari bertolak ke Singapura & Malaysia ✈️🇸🇬🇲🇾 #BeliaMendunia #SDAMWonosari #GoInternational',
        permalink: 'https://www.instagram.com/sdmujahidinwnsofficial/?hl=en',
        likes: '942',
        comments: '78',
        time: '1 HARI LALU',
    },
    {
        id: 2,
        img: 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=600&q=80',
        caption: 'Disiplin, tangkas, dan berwibawa! Penampilan memukau Polisi Cilik (Polcil) SDAM yang selalu menghadirkan kebanggaan di setiap formasi barisan 👮‍♂️✨ Salam Presisi! #PolcilSDAM #PolisiCilik #KarakterDisiplin',
        permalink: 'https://www.instagram.com/sdmujahidinwnsofficial/?hl=en',
        likes: '815',
        comments: '53',
        time: '3 HARI LALU',
    },
    {
        id: 3,
        img: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=600&q=80',
        caption: 'Khidmat dan penuh haru... Munaqasyah dan Wisuda Akbar Tahfidzul Qur’an ananda tercinta. Semoga mahkota kemuliaan senantiasa tersemat untuk kedua orang tua 📖🕌🤍 #TahfidzSDAM #AlMujahidinAward #GenerasiQurani',
        permalink: 'https://www.instagram.com/sdmujahidinwnsofficial/?hl=en',
        likes: '1.240',
        comments: '112',
        time: '5 HARI LALU',
    },
    {
        id: 4,
        img: 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=600&q=80',
        caption: 'Ceria, mandiri, dan tangguh di alam terbuka! Riang gembira kegiatan Kepanduan Hizbul Wathan (HW) Cilik dan pendekar Tapak Suci SDAM di Gadungsari 🏕️🥋🌿 #HizbulWathan #TapakSuciSDAM #SDAMJuara',
        permalink: 'https://www.instagram.com/sdmujahidinwnsofficial/?hl=en',
        likes: '730',
        comments: '46',
        time: '1 MINGGU LALU',
    },
];

const displayInstagramPosts = computed(() => {
    return (props.instagramPosts && props.instagramPosts.length > 0)
        ? props.instagramPosts
        : defaultInstagramPosts;
});
</script>

<template>
    <Head title="SD Muhammadiyah Al Mujahidin Wonosari - Belia Mendunia, Cerdas & Berakhlak" />

    <div class="min-h-screen bg-slate-950 text-slate-100 font-sans selection:bg-emerald-500 selection:text-white antialiased">
        
        <!-- TOP NOTIFICATION STRIP KHAS MUHAMMADIYAH WONOSARI -->
        <div class="bg-gradient-to-r from-emerald-950 via-teal-900 to-emerald-950 text-white text-xs font-medium py-2 px-4 shadow-sm border-b border-emerald-500/25">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-400 text-slate-950 shadow-sm uppercase tracking-wide">
                        PPDB / INDEN 2026/2027
                    </span>
                    <span class="truncate">Pendaftaran Siswa Baru SD Muhammadiyah Al Mujahidin Wonosari Telah Dibuka! Kuota Terbatas.</span>
                </div>
                <div class="flex items-center gap-4 text-[11px] text-emerald-200">
                    <span class="hidden md:inline">NPSN: 20402170 • Akreditasi A (Unggul) • Kab. Gunungkidul</span>
                    <a href="#kontak" class="hover:text-white underline transition">Info Kontak</a>
                </div>
            </div>
        </div>

        <!-- MAIN HEADER / NAVBAR -->
        <header class="sticky top-0 z-50 backdrop-blur-xl bg-slate-950/90 border-b border-slate-800/80 transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    
                    <!-- SCHOOL BRAND LOGO -->
                    <Link href="/" class="flex items-center gap-3.5 group">
                        <!-- EMBLEM LOGO SD MUHAMMADIYAH AL MUJAHIDIN -->
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 via-teal-500 to-amber-400 p-0.5 shadow-lg shadow-emerald-500/25 group-hover:scale-105 transition-transform">
                            <div class="w-full h-full bg-slate-950 rounded-[14px] flex items-center justify-center relative overflow-hidden">
                                <!-- SURYA MUHAMMADIYAH ICON -->
                                <svg class="w-7 h-7 text-amber-400 group-hover:text-amber-300 transition-colors" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l1.5 3.5L17 4l-1 3.5 3.5 1-2.5 2.5 3 2.5-3.5 1 1 3.5-3.5-1-1.5 3.5-1.5-3.5L5 20l1-3.5-3.5-1 2.5-2.5-3-2.5 3.5-1-1-3.5 3.5 1L12 2z"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xl sm:text-2xl font-black tracking-tight text-white font-mono">SD AL MUJAHIDIN</span>
                                <span class="hidden sm:inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    WONOSARI
                                </span>
                            </div>
                            <span class="text-[11px] text-emerald-400 block -mt-1 font-medium tracking-wide">
                                "Belia Mendunia" • Membangun Kecerdasan & Keshalehan
                            </span>
                        </div>
                    </Link>

                    <!-- DESKTOP NAV -->
                    <nav class="hidden lg:flex items-center gap-7 text-sm font-medium text-slate-300">
                        <a href="#profil" class="hover:text-emerald-400 transition-colors">Profil Sekolah</a>
                        <a href="#program" class="hover:text-emerald-400 transition-colors">Program Unggulan</a>
                        <a href="#fasilitas" class="hover:text-emerald-400 transition-colors">Fasilitas</a>
                        <a href="#berita" class="hover:text-emerald-400 transition-colors">Berita</a>
                        <a href="#instagram" class="hover:text-emerald-400 transition-colors">Instagram</a>
                        <a href="#kontak" class="hover:text-emerald-400 transition-colors">Kontak</a>
                    </nav>

                    <!-- PORTAL SIMSE & PPDB BUTTONS -->
                    <div class="hidden sm:flex items-center gap-3">
                        <template v-if="$page.props.auth && $page.props.auth.user">
                            <Link 
                                :href="route('dashboard')" 
                                class="inline-flex items-center gap-2 text-sm font-semibold text-white px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 shadow-md shadow-emerald-600/30 transition-all hover:scale-[1.02]"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                                <span>Dashboard SIMSE</span>
                            </Link>
                        </template>
                        <template v-else>
                            <Link 
                                v-if="canLogin" 
                                :href="route('login')" 
                                class="inline-flex items-center gap-2 text-sm font-semibold text-slate-200 hover:text-white px-4 py-2.5 rounded-xl hover:bg-slate-800/80 border border-slate-700/60 transition-colors"
                            >
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                                <span>Portal SIMSE</span>
                            </Link>
                            <Link 
                                v-if="canRegister" 
                                :href="route('register')" 
                                class="inline-flex items-center gap-2 text-sm font-semibold text-slate-950 px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-400 via-amber-300 to-yellow-400 hover:from-amber-300 hover:to-yellow-300 shadow-lg shadow-amber-500/25 transition-all hover:scale-105 active:scale-95 font-bold"
                            >
                                <span>Daftar Inden / PPDB</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </Link>
                        </template>
                    </div>

                    <!-- MOBILE TOGGLE -->
                    <button 
                        @click="mobileMenuOpen = !mobileMenuOpen" 
                        type="button" 
                        class="lg:hidden p-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/80 transition-colors"
                        aria-label="Menu"
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

            <!-- MOBILE MENU DRAWER -->
            <div v-show="mobileMenuOpen" class="lg:hidden border-b border-slate-800 bg-slate-950/95 backdrop-blur-2xl px-5 pt-3 pb-6 space-y-3">
                <a @click="mobileMenuOpen = false" href="#profil" class="block px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 font-medium">Profil Sekolah</a>
                <a @click="mobileMenuOpen = false" href="#program" class="block px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 font-medium">Program Unggulan</a>
                <a @click="mobileMenuOpen = false" href="#fasilitas" class="block px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 font-medium">Fasilitas</a>
                <a @click="mobileMenuOpen = false" href="#berita" class="block px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 font-medium">Berita & Prestasi</a>
                <a @click="mobileMenuOpen = false" href="#instagram" class="block px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 font-medium">Instagram @sdmujahidinwnsofficial</a>
                <a @click="mobileMenuOpen = false" href="#kontak" class="block px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 font-medium">Hubungi Kami</a>
                
                <div class="pt-4 border-t border-slate-800 flex flex-col gap-2.5">
                    <template v-if="$page.props.auth && $page.props.auth.user">
                        <Link :href="route('dashboard')" class="text-center w-full py-3 rounded-xl bg-emerald-600 text-white font-semibold shadow-md">
                            Buka Dashboard SIMSE
                        </Link>
                    </template>
                    <template v-else>
                        <Link v-if="canLogin" :href="route('login')" class="text-center w-full py-2.5 rounded-xl bg-slate-900 text-slate-200 font-semibold border border-slate-700">
                            Masuk Portal SIMSE
                        </Link>
                        <Link v-if="canRegister" :href="route('register')" class="text-center w-full py-3 rounded-xl bg-amber-400 text-slate-950 font-bold shadow-md">
                            Daftar Inden / PPDB Online
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- 1. HERO SECTION AL MUJAHIDIN WONOSARI -->
        <section class="relative pt-12 pb-20 lg:pt-20 lg:pb-32 overflow-hidden">
            <!-- BACKGROUND GLOWS (EMERALD & AMBER ACCENTS) -->
            <div class="absolute inset-0 pointer-events-none z-0">
                <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[700px] lg:w-[1200px] h-[550px] bg-gradient-to-tr from-emerald-600/20 via-teal-600/15 to-amber-500/15 rounded-full blur-[140px] opacity-75"></div>
                <div class="absolute top-1/3 -left-40 w-96 h-96 bg-emerald-600/15 rounded-full blur-[120px]"></div>
                <div class="absolute inset-0 bg-[linear-gradient(to_right,#33415512_1px,transparent_1px),linear-gradient(to_bottom,#33415512_1px,transparent_1px)] bg-[size:3.5rem_3.5rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                    
                    <!-- HERO TEXT -->
                    <div class="lg:col-span-7 text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/25 text-emerald-300 text-xs sm:text-sm font-semibold mb-6 backdrop-blur-md">
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                            <span>Sekolah Dasar Islam Terpadu & Rujukan Unggulan Gunungkidul</span>
                            <span class="text-slate-500">|</span>
                            <span class="text-slate-300 font-normal">T.A 2026/2027</span>
                        </div>

                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.15] mb-6">
                            Mewujudkan Generasi <br class="hidden sm:inline" />
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-200 to-amber-300">
                                "Belia Mendunia" yang Cerdas & Shalih
                            </span>
                        </h1>

                        <p class="text-base sm:text-lg text-slate-300 max-w-2xl mx-auto lg:mx-0 mb-8 font-normal leading-relaxed">
                            Selamat datang di <strong>SD Muhammadiyah Al Mujahidin Wonosari</strong>. Kami menghadirkan pendidikan dasar Islam terpadu yang memadukan kedalaman Al-Qur’an (Tahfidz), penanaman karakter akhlakul karimah, wawasan internasional (International Exchange), serta modernitas tata kelola sekolah cerdas berbasis <strong>SIMSE</strong>.
                        </p>

                        <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 mb-10">
                            <Link 
                                v-if="canRegister"
                                :href="route('register')" 
                                class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-gradient-to-r from-emerald-600 via-emerald-500 to-teal-600 text-white font-bold text-base shadow-xl shadow-emerald-600/30 hover:shadow-emerald-600/50 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-3"
                            >
                                <span>Pendaftaran Siswa Baru (Inden)</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </Link>

                            <a 
                                href="#profil" 
                                class="w-full sm:w-auto px-7 py-4 rounded-2xl bg-slate-900/90 hover:bg-slate-850 text-slate-200 font-semibold text-base border border-slate-700/80 backdrop-blur-md hover:border-slate-600 transition-all flex items-center justify-center gap-2"
                            >
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                <span>Profil & Sambutan Kepala Sekolah</span>
                            </a>
                        </div>

                        <!-- TRUST BADGES -->
                        <div class="pt-6 border-t border-slate-800/80 flex flex-wrap items-center justify-center lg:justify-start gap-6 text-xs text-slate-400">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>Terakreditasi 'A' (Unggul) • NPSN: 20402170</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>The Best Favorite Elementary Islamic School of The Year</span>
                            </div>
                        </div>
                    </div>

                    <!-- HERO VISUAL CARD -->
                    <div class="lg:col-span-5 relative">
                        <div class="relative mx-auto max-w-md lg:max-w-none">
                            <div class="rounded-3xl p-2.5 bg-gradient-to-b from-slate-700/50 via-slate-800/30 to-slate-900 border border-slate-700/60 shadow-2xl shadow-emerald-950/60">
                                <div class="relative rounded-2xl overflow-hidden aspect-[4/3] sm:aspect-[16/11]">
                                    <img 
                                        src="https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=1000&q=80" 
                                        alt="Aktivitas Belajar Siswa SD Muhammadiyah Al Mujahidin" 
                                        class="w-full h-full object-cover transform hover:scale-105 transition-transform duration-700"
                                    />
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/20 to-transparent"></div>
                                    <div class="absolute bottom-4 left-4 right-4">
                                        <div class="p-3.5 rounded-xl bg-slate-900/85 backdrop-blur-md border border-slate-700/60 flex items-center justify-between">
                                            <div>
                                                <div class="text-xs text-slate-400">Kampus Gadungsari Wonosari</div>
                                                <div class="text-sm font-bold text-white">Full Day School Ramah Bakat</div>
                                            </div>
                                            <span class="px-2.5 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 text-xs font-mono font-bold">
                                                SDAM
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- FLOATING PROGRAM PILL -->
                            <div class="absolute -bottom-6 -left-4 sm:-left-6 p-4 rounded-2xl bg-slate-900/95 border border-slate-700/80 shadow-xl backdrop-blur-xl flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold text-xl">
                                    ✈️
                                </div>
                                <div>
                                    <div class="text-xs text-slate-400 font-medium">Program Go International</div>
                                    <div class="text-base font-black text-white font-mono">Singapore & Malaysia Exchange</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- QUICK STATS BAR -->
                <div class="mt-20 pt-10 border-t border-slate-800/80 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                    <div class="p-5 rounded-2xl bg-slate-900/40 border border-slate-800/70">
                        <div class="text-3xl sm:text-4xl font-black text-emerald-400 font-mono">900+</div>
                        <div class="text-xs sm:text-sm text-slate-200 font-semibold mt-1">Siswa-Siswi Berprestasi</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Kelas 1 s.d. 6 • Sistem Inden</div>
                    </div>
                    <div class="p-5 rounded-2xl bg-slate-900/40 border border-slate-800/70">
                        <div class="text-3xl sm:text-4xl font-black text-amber-400 font-mono">60+</div>
                        <div class="text-xs sm:text-sm text-slate-200 font-semibold mt-1">Ustadz & Ustadzah</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Pendidik Berdedikasi & Bersertifikasi</div>
                    </div>
                    <div class="p-5 rounded-2xl bg-slate-900/40 border border-slate-800/70">
                        <div class="text-3xl sm:text-4xl font-black text-teal-400 font-mono">25+</div>
                        <div class="text-xs sm:text-sm text-slate-200 font-semibold mt-1">Ekstrakurikuler Pilihan</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Polcil, HW, Tapak Suci, Drumband</div>
                    </div>
                    <div class="p-5 rounded-2xl bg-slate-900/40 border border-slate-800/70">
                        <div class="text-3xl sm:text-4xl font-black text-blue-400 font-mono">100%</div>
                        <div class="text-xs sm:text-sm text-slate-200 font-semibold mt-1">Terkoneksi SIMSE</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Presensi WA, e-Kantin & Tahfidz</div>
                    </div>
                </div>

            </div>
        </section>

        <!-- 2. PROFIL SEKOLAH & SAMBUTAN KEPALA SEKOLAH -->
        <section id="profil" class="py-24 bg-slate-900 relative border-t border-slate-800/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="text-xs font-bold tracking-widest text-emerald-400 uppercase bg-emerald-500/10 px-3.5 py-1.5 rounded-full border border-emerald-500/20">
                        Profil Lembaga
                    </span>
                    <h2 class="text-3xl sm:text-5xl font-black text-white mt-4 tracking-tight">
                        Membangun Kecerdasan & Keshalehan
                    </h2>
                    <p class="mt-4 text-slate-400 text-base sm:text-lg">
                        Berdiri di bawah naungan Pimpinan Daerah Muhammadiyah Gunungkidul, SD Muhammadiyah Al Mujahidin hadir menjadi pionir sekolah dasar Islam terpadu yang memadukan adab, sains, dan wawasan mendunia.
                    </p>
                </div>

                <!-- SAMBUTAN KEPALA SEKOLAH JOKO KISWANTO, S.Pd. -->
                <div class="rounded-3xl bg-slate-950 p-8 lg:p-12 border border-slate-800 shadow-xl mb-14">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                        <div class="lg:col-span-4 text-center lg:text-left">
                            <div class="relative inline-block">
                                <div class="w-48 h-48 sm:w-56 sm:h-56 rounded-3xl overflow-hidden mx-auto border-2 border-emerald-500/40 shadow-2xl">
                                    <img 
                                        src="https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=600&q=80" 
                                        alt="Kepala Sekolah SD Muhammadiyah Al Mujahidin" 
                                        class="w-full h-full object-cover"
                                    />
                                </div>
                                <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 px-3.5 py-1 rounded-full bg-emerald-600 text-white text-[11px] font-bold shadow-lg uppercase tracking-wider whitespace-nowrap">
                                    Kepala Sekolah
                                </div>
                            </div>
                            <div class="mt-5">
                                <h4 class="text-lg font-bold text-white">Joko Kiswanto, S.Pd.</h4>
                                <p class="text-xs text-emerald-400 font-medium">Kepala SD Muhammadiyah Al Mujahidin Wonosari</p>
                            </div>
                        </div>

                        <div class="lg:col-span-8">
                            <div class="text-sm font-semibold text-emerald-400 mb-2 font-mono">
                                Assalamu’alaikum Warahmatullahi Wabarakatuh
                            </div>
                            <span class="text-3xl sm:text-4xl text-emerald-400 font-serif leading-none block mb-2">“</span>
                            <blockquote class="text-base sm:text-lg text-slate-200 leading-relaxed italic mb-6">
                                Kami meyakini bahwa setiap anak lahir dengan fitrah kebaikan dan keunikan potensi kecerdasan. Melalui semboyan <strong>"Belia Mendunia"</strong>, SD Muhammadiyah Al Mujahidin Wonosari berkomitmen tidak hanya mendidik ananda pandai membaca dan menghafal Al-Qur'an, tetapi juga memiliki mentalitas tangguh, budi pekerti yang santun, serta kepercayaan diri untuk berprestasi di panggung global. Dengan implementasi sistem <strong>SIMSE</strong>, sinergi antara ustadz/ustadzah dan ayah-bunda terjalin semakin transparan, cepat, dan penuh keberkahan.
                            </blockquote>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-6 border-t border-slate-850 text-xs">
                                <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800">
                                    <div class="font-bold text-white mb-1">Sekolah Ramah Bakat</div>
                                    <p class="text-slate-400">Pembelajaran integratif tanpa tekanan gawai berlebih.</p>
                                </div>
                                <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800">
                                    <div class="font-bold text-white mb-1">Akreditasi A Unggul</div>
                                    <p class="text-slate-400">NPSN: 20402170 dengan predikat sekolah rujukan nasional.</p>
                                </div>
                                <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800">
                                    <div class="font-bold text-white mb-1">Program Belia Mendunia</div>
                                    <p class="text-slate-400">International exchange ke Singapura & Malaysia.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- VISI & MISI RESMI SD MUHAMMADIYAH AL MUJAHIDIN -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- VISI RESMI -->
                    <div class="p-8 rounded-3xl bg-slate-950/70 border border-slate-800 hover:border-emerald-500/40 transition-colors">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-white">Visi Sekolah</h3>
                        </div>
                        <p class="text-slate-300 text-sm leading-relaxed mb-6 font-medium italic">
                            "Menjadi Sekolah Dasar Islam Terpadu yang unggul dalam bidang akademik bertumpu pada pribadi yang bertaqwa dan berakhlaqul karimah."
                        </p>
                        <div class="flex flex-wrap gap-2 text-xs">
                            <span class="px-3 py-1 rounded-full bg-slate-900 text-emerald-300 border border-slate-800">Ketaqwaan</span>
                            <span class="px-3 py-1 rounded-full bg-slate-900 text-emerald-300 border border-slate-800">Akhlaqul Karimah</span>
                            <span class="px-3 py-1 rounded-full bg-slate-900 text-emerald-300 border border-slate-800">Akademik Unggul</span>
                        </div>
                    </div>

                    <!-- MISI RESMI -->
                    <div class="p-8 rounded-3xl bg-slate-950/70 border border-slate-800 hover:border-amber-500/40 transition-colors">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-white">Misi Utama</h3>
                        </div>
                        <ul class="space-y-2.5 text-xs sm:text-sm text-slate-300">
                            <li class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold shrink-0 mt-0.5 text-xs">1</span>
                                <span>Meningkatkan wawasan Al-Islam membentuk pribadi yang beriman, bertaqwa, dan berakhlak mulia.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold shrink-0 mt-0.5 text-xs">2</span>
                                <span>Menjadikan nilai Al-Qur'an dan As-Sunnah sebagai rujukan utama proses pendidikan.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold shrink-0 mt-0.5 text-xs">3</span>
                                <span>Meningkatkan kemampuan pemahaman dan hafalan Al-Qur'an (Tahfiz).</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold shrink-0 mt-0.5 text-xs">4</span>
                                <span>Meningkatkan wawasan keilmuan, penguasaan bahasa asing, dan teknologi informasi.</span>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </section>

        <!-- 3. PROGRAM UNGGULAN -->
        <section id="program" class="py-24 bg-slate-950 relative border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="text-xs font-bold tracking-widest text-emerald-400 uppercase bg-emerald-500/10 px-3.5 py-1.5 rounded-full border border-emerald-500/20">
                        Keunggulan Pendidikan
                    </span>
                    <h2 class="text-3xl sm:text-5xl font-black text-white mt-4 tracking-tight">
                        Program Unggulan Al Mujahidin
                    </h2>
                    <p class="mt-4 text-slate-400 text-base sm:text-lg">
                        Mempersiapkan ananda menjadi generasi berkarakter unggul, berjiwa Al-Qur'an, cerdas berpikir ilmiah, dan percaya diri melangkah ke kancah global.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div 
                        v-for="(prog, idx) in programs" 
                        :key="idx"
                        class="p-8 rounded-3xl bg-slate-900/60 border border-slate-800/90 hover:border-slate-700 hover:bg-slate-900 transition-all duration-300 group flex flex-col justify-between"
                    >
                        <div>
                            <div class="flex items-center justify-between mb-6">
                                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-md bg-gradient-to-tr" :class="prog.color">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="prog.icon" />
                                    </svg>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-300 border border-slate-700/60">
                                    {{ prog.badge }}
                                </span>
                            </div>

                            <div class="text-xs font-semibold text-emerald-400 uppercase tracking-wider mb-1">
                                {{ prog.category }}
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-3 group-hover:text-emerald-300 transition-colors">
                                {{ prog.title }}
                            </h3>
                            <p class="text-sm text-slate-300 leading-relaxed mb-6">
                                {{ prog.desc }}
                            </p>
                        </div>

                        <div class="pt-6 border-t border-slate-800/80">
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Pilar & Kegiatan Program:</div>
                            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs text-slate-300">
                                <li v-for="(feat, fIdx) in prog.features" :key="fIdx" class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    <span>{{ feat }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- 4. FASILITAS KAMPUS GADUNGSARI -->
        <section id="fasilitas" class="py-24 bg-slate-900 relative border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
                    <div>
                        <span class="text-xs font-bold tracking-widest text-emerald-400 uppercase bg-emerald-500/10 px-3.5 py-1.5 rounded-full border border-emerald-500/20">
                            Sarana & Fasilitas
                        </span>
                        <h2 class="text-3xl sm:text-5xl font-black text-white mt-4 tracking-tight">
                            Fasilitas Kampus Gadungsari
                        </h2>
                        <p class="mt-3 text-slate-400 max-w-2xl text-base">
                            Didukung sarana prasarana modern, ruang belajar ber-AC, masjid luas, area terbuka hijau, dan ekosistem digital SIMSE yang nyaman untuk ananda.
                        </p>
                    </div>
                    <div class="text-left md:text-right">
                        <span class="text-xs text-slate-400 block font-medium">Standar Kampus:</span>
                        <span class="text-sm font-bold text-amber-400 flex items-center gap-1.5 justify-start md:justify-end mt-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            Full Day School Ramah Anak & Ber-AC
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div 
                        v-for="(fac, index) in facilities" 
                        :key="index"
                        class="rounded-3xl bg-slate-950 border border-slate-800 overflow-hidden hover:border-slate-700 transition-all duration-300 group flex flex-col justify-between"
                    >
                        <div>
                            <div class="relative aspect-[16/10] overflow-hidden bg-slate-900">
                                <img 
                                    :src="fac.img" 
                                    :alt="fac.name"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                />
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent"></div>
                                <span class="absolute top-4 right-4 px-3 py-1 rounded-full bg-slate-950/80 backdrop-blur-md text-[11px] font-bold text-emerald-300 border border-slate-700/60">
                                    {{ fac.category }}
                                </span>
                            </div>

                            <div class="p-6">
                                <h3 class="text-xl font-bold text-white mb-2 group-hover:text-emerald-400 transition-colors">
                                    {{ fac.name }}
                                </h3>
                                <p class="text-xs text-slate-400 leading-relaxed">
                                    {{ fac.desc }}
                                </p>
                            </div>
                        </div>

                        <div class="px-6 pb-6 pt-3 border-t border-slate-900 flex items-center justify-between text-xs font-mono text-slate-400">
                            <span class="text-emerald-400 font-semibold">{{ fac.spec }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- 5. BERITA & AGENDA SEKOLAH -->
        <section id="berita" class="py-24 bg-slate-950 relative border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
                    <div>
                        <span class="text-xs font-bold tracking-widest text-emerald-400 uppercase bg-emerald-500/10 px-3.5 py-1.5 rounded-full border border-emerald-500/20">
                            Warta & Prestasi
                        </span>
                        <h2 class="text-3xl sm:text-5xl font-black text-white mt-4 tracking-tight">
                            Kabar Terkini Al Mujahidin
                        </h2>
                        <p class="mt-3 text-slate-400 max-w-2xl text-base">
                            Ragam prestasi membanggakan, agenda kemitraan internasional, serta aktivitas kesiswaan SD Muhammadiyah Al Mujahidin Wonosari.
                        </p>
                    </div>
                    <div class="shrink-0">
                        <a 
                            href="#berita" 
                            class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-400 hover:text-emerald-300 transition-colors px-4 py-2 rounded-xl bg-slate-900 border border-slate-800"
                        >
                            <span>Lihat Semua Warta</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <article 
                        v-for="item in newsList" 
                        :key="item.id"
                        class="rounded-3xl bg-slate-900/70 border border-slate-800 overflow-hidden hover:border-slate-700 hover:bg-slate-900 transition-all duration-300 group flex flex-col justify-between"
                    >
                        <div>
                            <div class="relative aspect-[16/10] overflow-hidden bg-slate-950">
                                <img 
                                    :src="item.img" 
                                    :alt="item.title"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                />
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>
                                <span class="absolute top-4 left-4 px-3 py-1 rounded-full text-xs font-bold border backdrop-blur-md" :class="item.badgeColor">
                                    {{ item.category }}
                                </span>
                            </div>

                            <div class="p-6">
                                <div class="flex items-center gap-3 text-xs text-slate-400 mb-3">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        {{ item.date }}
                                    </span>
                                    <span>•</span>
                                    <span>{{ item.readTime }}</span>
                                </div>

                                <h3 class="text-lg font-bold text-white mb-3 group-hover:text-emerald-300 transition-colors line-clamp-2">
                                    {{ item.title }}
                                </h3>
                                <p class="text-xs text-slate-300 leading-relaxed line-clamp-3">
                                    {{ item.excerpt }}
                                </p>
                            </div>
                        </div>

                        <div class="p-6 pt-0">
                            <button 
                                type="button" 
                                class="text-xs font-bold text-emerald-400 group-hover:text-emerald-300 inline-flex items-center gap-1.5 transition-colors"
                            >
                                <span>Baca Selengkapnya</span>
                                <svg class="w-3.5 h-3.5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </button>
                        </div>
                    </article>
                </div>

            </div>
        </section>

        <!-- 6. 4 POSTINGAN TERBARU INSTAGRAM @sdmujahidinwnsofficial -->
        <section id="instagram" class="py-24 bg-slate-900 relative border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- INSTAGRAM PROFILE BAR -->
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-12 p-6 rounded-3xl bg-slate-950 border border-slate-800 gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full p-1 bg-gradient-to-tr from-amber-500 via-emerald-500 to-teal-500 shadow-lg shrink-0">
                            <div class="w-full h-full rounded-full bg-slate-950 p-1 flex items-center justify-center">
                                <svg class="w-8 h-8 text-amber-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l1.5 3.5L17 4l-1 3.5 3.5 1-2.5 2.5 3 2.5-3.5 1 1 3.5-3.5-1-1.5 3.5-1.5-3.5L5 20l1-3.5-3.5-1 2.5-2.5-3-2.5 3.5-1-1-3.5 3.5 1L12 2z"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-black text-white font-mono">@sdmujahidinwnsofficial</h3>
                                <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Akun Resmi SD Muhammadiyah Al Mujahidin Wonosari, Gunungkidul • <strong>Belia Mendunia</strong>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <a 
                            href="https://www.instagram.com/sdmujahidinwnsofficial/?hl=en" 
                            target="_blank" 
                            rel="noopener noreferrer"
                            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-pink-600 via-rose-500 to-amber-500 text-white font-bold text-xs shadow-md hover:scale-105 transition-all inline-flex items-center gap-2"
                        >
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            <span>Buka Instagram Resmi</span>
                        </a>
                    </div>
                </div>

                <!-- 4 POSTINGAN INSTAGRAM 1:1 -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                    <a 
                        v-for="post in displayInstagramPosts" 
                        :key="post.id"
                        :href="post.permalink || 'https://www.instagram.com/sdmujahidinwnsofficial/?hl=en'"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group relative rounded-2xl overflow-hidden aspect-square bg-slate-950 border border-slate-800 shadow-md cursor-pointer block"
                    >
                        <img 
                            :src="post.img" 
                            :alt="post.caption" 
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        />
                        
                        <!-- HOVER OVERLAY -->
                        <div class="absolute inset-0 bg-slate-950/85 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity duration-300 p-5 flex flex-col justify-between text-white">
                            <div class="flex items-center justify-between text-xs font-mono text-slate-400">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 fill-current text-pink-500" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                    {{ post.likes }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 fill-current text-emerald-400" viewBox="0 0 24 24"><path d="M21.99 4c0-1.1-.89-2-1.99-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4-.01-18z"/></svg>
                                    {{ post.comments }}
                                </span>
                            </div>

                            <p class="text-xs text-slate-200 line-clamp-4 leading-relaxed font-normal">
                                {{ post.caption }}
                            </p>

                            <div class="text-[10px] font-mono text-slate-400 tracking-wider">
                                {{ post.time }}
                            </div>
                        </div>

                        <!-- INSTAGRAM GLYPH BADGE -->
                        <div class="absolute top-2.5 right-2.5 w-6 h-6 rounded-full bg-slate-950/70 backdrop-blur-md flex items-center justify-center text-white group-hover:opacity-0 transition-opacity">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </div>
                    </a>
                </div>

            </div>
        </section>

        <!-- 7. INTEGRASI SISTEM SIMSE UNTUK WALI SANTRI & GURU -->
        <section class="py-20 bg-gradient-to-br from-emerald-950 via-slate-950 to-teal-950 border-t border-slate-800 text-center relative overflow-hidden">
            <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <span class="px-4 py-1.5 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold uppercase tracking-wider border border-emerald-500/30">
                    Sistem Digital SIMSE Al Mujahidin
                </span>
                
                <h2 class="text-3xl sm:text-5xl font-black text-white mt-6 mb-6 tracking-tight">
                    Kemitraan Nyata Sekolah & Orang Tua
                </h2>
                
                <p class="max-w-2xl mx-auto text-slate-300 text-base sm:text-lg mb-8 leading-relaxed">
                    Presensi kedatangan ananda dikirim otomatis ke WhatsApp orang tua. Pantau mutaba’ah hafalan tahfidz Al-Qur'an, capaian nilai berkala, dan limit jajan sehat di e-Kantin melalui portal digital SIMSE.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <Link 
                        v-if="canLogin" 
                        :href="route('login')" 
                        class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-white text-slate-950 font-bold text-sm shadow-xl hover:bg-slate-100 transition-all flex items-center justify-center gap-2"
                    >
                        <span>Masuk Portal SIMSE</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </Link>

                    <Link 
                        v-if="canRegister" 
                        :href="route('register')" 
                        class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-sm shadow-lg shadow-amber-500/25 transition-all flex items-center justify-center gap-2"
                    >
                        <span>Pendaftaran Inden / PPDB</span>
                    </Link>
                </div>
            </div>
        </section>

        <!-- 8. FOOTER & KONTAK OTENTIK KAMPUS WONOSARI -->
        <footer id="kontak" class="bg-slate-950 text-slate-400 py-16 border-t border-slate-850">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-12">
                    
                    <!-- BRAND & ALAMAT OTENTIK -->
                    <div class="md:col-span-2">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-amber-500 flex items-center justify-center text-white shadow-md">
                                <svg class="w-6 h-6 text-amber-200" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l1.5 3.5L17 4l-1 3.5 3.5 1-2.5 2.5 3 2.5-3.5 1 1 3.5-3.5-1-1.5 3.5-1.5-3.5L5 20l1-3.5-3.5-1 2.5-2.5-3-2.5 3.5-1-1-3.5 3.5 1L12 2z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-xl font-black text-white font-mono tracking-tight block">SD MUHAMMADIYAH AL MUJAHIDIN</span>
                                <span class="text-[11px] text-emerald-400">NPSN: 20402170 • Akreditasi A (Unggul) • Majelis Dikdasmen PDM Gunungkidul</span>
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 max-w-md leading-relaxed mb-4">
                            Jl. Mayang, Gadungsari, Kelurahan Wonosari, Kapanewon Wonosari, Kabupaten Gunungkidul, D.I. Yogyakarta 55812.
                        </p>
                        <div class="space-y-1.5 text-xs text-slate-300">
                            <div class="flex items-center gap-2">
                                <span class="text-slate-400">Telepon / Fax:</span>
                                <span class="font-mono text-white">(0274) 391147</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-slate-400">Email Resmi:</span>
                                <span class="text-emerald-400 font-mono">admin@sdmujahidin-wns.sch.id</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-slate-400">Website Resmi:</span>
                                <a href="https://www.sdmujahidin-wns.sch.id" target="_blank" rel="noopener noreferrer" class="text-amber-400 hover:underline font-mono">
                                    www.sdmujahidin-wns.sch.id
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- LINK NAVIGASI -->
                    <div>
                        <h4 class="text-white font-bold text-sm mb-4 uppercase tracking-wider">Navigasi Utama</h4>
                        <ul class="space-y-2 text-xs">
                            <li><a href="#profil" class="hover:text-emerald-400 transition-colors">Sambutan Kepala Sekolah</a></li>
                            <li><a href="#program" class="hover:text-emerald-400 transition-colors">Program Belia Mendunia</a></li>
                            <li><a href="#program" class="hover:text-emerald-400 transition-colors">Tahfidzul Qur’an & Award</a></li>
                            <li><a href="#fasilitas" class="hover:text-emerald-400 transition-colors">Fasilitas Kampus Gadungsari</a></li>
                            <li><a href="#berita" class="hover:text-emerald-400 transition-colors">Warta & Prestasi Siswa</a></li>
                            <li><a href="#instagram" class="hover:text-emerald-400 transition-colors">Instagram @sdmujahidinwnsofficial</a></li>
                        </ul>
                    </div>

                    <!-- PORTAL & LAYANAN TERPADU -->
                    <div>
                        <h4 class="text-white font-bold text-sm mb-4 uppercase tracking-wider">Layanan Terpadu</h4>
                        <ul class="space-y-2 text-xs">
                            <li><Link :href="route('login', { role: 'guru' })" class="hover:text-emerald-400 transition-colors">Portal Guru & Pendidik</Link></li>
                            <li><Link :href="route('login', { role: 'siswa' })" class="hover:text-emerald-400 transition-colors">Portal Siswa & Wali Santri</Link></li>
                            <li><Link :href="route('login', { role: 'staf' })" class="hover:text-emerald-400 transition-colors">Portal Staf & Bendahara SPP</Link></li>
                            <li><Link :href="route('login', { role: 'admin' })" class="hover:text-emerald-400 transition-colors">Portal Master Administrator</Link></li>
                            <li><Link v-if="canRegister" :href="route('register')" class="text-amber-400 hover:text-amber-300 transition-colors font-bold">Pendaftaran Siswa Baru (Inden) →</Link></li>
                        </ul>
                    </div>

                </div>

                <div class="pt-8 border-t border-slate-850 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-4">
                    <p>© 2026 SD Muhammadiyah Al Mujahidin Wonosari. Membangun Kecerdasan & Keshalehan. Terintegrasi SIMSE.</p>
                    <div class="flex items-center gap-6 text-[11px]">
                        <a href="https://www.instagram.com/sdmujahidinwnsofficial/?hl=en" target="_blank" rel="noopener noreferrer" class="hover:text-pink-400 transition-colors">@sdmujahidinwnsofficial</a>
                        <a href="#" class="hover:text-slate-300">Majelis Dikdasmen PDM Gunungkidul</a>
                    </div>
                </div>
            </div>
        </footer>

    </div>
</template>
