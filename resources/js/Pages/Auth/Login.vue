<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    canResetPassword: {
        type: Boolean,
        default: true,
    },
    status: {
        type: String,
        default: '',
    },
    initialRole: {
        type: String,
        default: 'guru',
    },
    otpSentPhone: {
        type: String,
        default: '',
    },
});

// Definisi 4 Jalur Login Peran
const roles = [
    {
        id: 'guru',
        title: 'Guru & Pendidik',
        shortTitle: 'Guru',
        badge: 'Pendidik & Ustadz/ah',
        portalName: 'Portal Akademik Guru',
        desc: 'Akses jadwal mengajar, e-Raport, absensi kelas, dan bank soal ujian CBT.',
        placeholder: 'nama.guru@sdmujahidin-wns.sch.id / NIP',
        accentBg: 'from-emerald-600 via-emerald-700 to-teal-800',
        badgeBg: 'bg-emerald-50 text-emerald-700 border-emerald-200',
        btnBg: 'from-emerald-600 via-teal-600 to-emerald-600 hover:from-emerald-500 hover:to-teal-500 shadow-emerald-700/20',
        focusRing: 'focus:border-emerald-600 focus:ring-emerald-500/10',
        activeTab: 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30',
        icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        features: [
            'Input Nilai Harian, PTS, dan PAS Otomatis',
            'Manajemen Jadwal Mengajar Bebas Bentrok',
            'Ujian Online CBT Anti-Curang & Tugas LMS',
            'Rekapitulasi Slip Gaji & Payroll Transparan'
        ],
    },
    {
        id: 'siswa',
        title: 'Siswa & Santri',
        shortTitle: 'Siswa',
        badge: 'Peserta Didik',
        portalName: 'Portal Akademik Siswa',
        desc: 'Pantau jadwal pelajaran, presensi harian, materi LMS, tugas, dan ujian CBT.',
        placeholder: 'nisn.siswa@sdmujahidin-wns.sch.id / NISN',
        accentBg: 'from-sky-600 via-blue-700 to-indigo-800',
        badgeBg: 'bg-sky-50 text-sky-700 border-sky-200',
        btnBg: 'from-sky-600 via-blue-600 to-indigo-600 hover:from-sky-500 hover:to-blue-500 shadow-sky-700/20',
        focusRing: 'focus:border-sky-600 focus:ring-sky-500/10',
        activeTab: 'bg-sky-600 text-white shadow-md shadow-sky-600/30',
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        features: [
            'KTS Digital Ber-QR Code untuk Presensi & Perpus',
            'Akses Materi Ajar Digital & Pengumpulan Tugas',
            'Ujian Online CBT & Pemantauan Nilai Harian',
            'Capaian Mutaba\'ah Hafalan Tahfidz Qur\'an'
        ],
    },
    {
        id: 'ortu',
        title: 'Orang Tua / Wali',
        shortTitle: 'Ortu',
        badge: 'Wali Santri',
        portalName: 'Portal Wali Santri',
        desc: 'Pantau kehadiran anak, e-Raport, tagihan SPP, dan saldo jajan sehat e-Kantin.',
        placeholder: 'wali.santri@sdmujahidin-wns.sch.id',
        accentBg: 'from-cyan-600 via-teal-700 to-emerald-800',
        badgeBg: 'bg-cyan-50 text-cyan-700 border-cyan-200',
        btnBg: 'from-cyan-600 via-teal-600 to-emerald-600 hover:from-cyan-500 hover:to-teal-500 shadow-cyan-700/20',
        focusRing: 'focus:border-cyan-600 focus:ring-cyan-500/10',
        activeTab: 'bg-cyan-600 text-white shadow-md shadow-cyan-600/30',
        icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
        features: [
            'Notifikasi Kehadiran Anak Otomatis ke WhatsApp',
            'Monitoring Tagihan SPP & Riwayat Pembayaran',
            'Pantau Saldo & Riwayat Jajan Sehat e-Kantin',
            'Laporan Perkembangan Hafalan & e-Raport'
        ],
    },
    {
        id: 'staf',
        title: 'Staf Sekolah',
        shortTitle: 'Staf',
        badge: 'Tata Usaha & Layanan',
        portalName: 'Portal Staf & Tata Usaha',
        desc: 'Layanan administrasi sekolah, sirkulasi perpustakaan, absensi, dan operasional kantin.',
        placeholder: 'staf@sdmujahidin-wns.sch.id',
        accentBg: 'from-amber-600 via-orange-600 to-amber-700',
        badgeBg: 'bg-amber-50 text-amber-700 border-amber-200',
        btnBg: 'from-amber-600 via-orange-600 to-amber-600 hover:from-amber-500 hover:to-orange-500 shadow-amber-700/20',
        focusRing: 'focus:border-amber-600 focus:ring-amber-500/10',
        activeTab: 'bg-amber-600 text-white shadow-md shadow-amber-600/30',
        icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        features: [
            'Layanan Sirkulasi Peminjaman Perpustakaan',
            'Presensi Kehadiran Pegawai & Staf Harian',
            'Administrasi Surat & Arsip Tata Usaha',
            'Akses Rincian Slip Gaji & Payroll Bulanan'
        ],
    },
    {
        id: 'bendahara',
        title: 'Bendahara',
        shortTitle: 'Bendahara',
        badge: 'Keuangan & Kas Sekolah',
        portalName: 'Portal Keuangan & Bendahara',
        desc: 'Pengelolaan tagihan SPP siswa, kasir kas sekolah, dan rekapitulasi payroll pegawai.',
        placeholder: 'bendahara@sdmujahidin-wns.sch.id',
        accentBg: 'from-rose-600 via-pink-700 to-rose-800',
        badgeBg: 'bg-rose-50 text-rose-700 border-rose-200',
        btnBg: 'from-rose-600 via-pink-600 to-rose-600 hover:from-rose-500 hover:to-pink-500 shadow-rose-700/20',
        focusRing: 'focus:border-rose-600 focus:ring-rose-500/10',
        activeTab: 'bg-rose-600 text-white shadow-md shadow-rose-600/30',
        icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
        features: [
            'Pembukuan Tagihan SPP & Invoice Otomatis',
            'Point of Sales (POS) Kasir Kas Sekolah',
            'Kalkulasi Honor Jam Mengajar & Slip Gaji',
            'Laporan Neraca Keuangan Harian & Bulanan'
        ],
    },
    {
        id: 'admin',
        title: 'Administrator',
        shortTitle: 'Admin',
        badge: 'Super Admin & Operator',
        portalName: 'Portal Master Administrator',
        desc: 'Kendali penuh hak akses, sinkronisasi data DAPODIK, jadwal otomatis, dan manajemen modul.',
        placeholder: 'admin@sdmujahidin-wns.sch.id',
        accentBg: 'from-purple-700 via-indigo-800 to-slate-900',
        badgeBg: 'bg-purple-50 text-purple-700 border-purple-200',
        btnBg: 'from-purple-600 via-indigo-600 to-purple-600 hover:from-purple-500 hover:to-indigo-500 shadow-purple-700/20',
        focusRing: 'focus:border-purple-600 focus:ring-purple-500/10',
        activeTab: 'bg-purple-600 text-white shadow-md shadow-purple-600/30',
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
        features: [
            'Auto-Generate Jadwal Cerdas Tanpa Bentrok',
            'Sinkronisasi & Export-Import Excel DAPODIK',
            'Manajemen 11 Peran Pengguna & Otoritas Modul',
            'Monitoring Log Aktivitas & Backup Sistem'
        ],
    },
];

const selectedRole = ref(props.initialRole || 'guru');
const showPassword = ref(false);
const parentAuthMode = ref('otp');
const otpStepRequested = ref(Boolean(props.otpSentPhone));

onMounted(() => {
    // Ambil parameter ?role= dari URL jika ada
    const urlParams = new URLSearchParams(window.location.search);
    const roleParam = urlParams.get('role');
    if (roleParam && roles.some(r => r.id === roleParam)) {
        selectedRole.value = roleParam;
    }
});

const roleAlias = {
    bk: 'guru',
    kantin: 'staf',
    ppdb: 'siswa',
};

const currentRoleData = computed(() => {
    const resolvedId = roleAlias[selectedRole.value] || selectedRole.value;
    return roles.find(r => r.id === resolvedId) || roles[0];
});

const isParentOtpView = computed(() => selectedRole.value === 'ortu' && parentAuthMode.value === 'otp');

const form = useForm({
    email: '',
    password: '',
    remember: false,
    selected_role: selectedRole.value,
});

const otpRequestForm = useForm({
    phone_number: props.otpSentPhone || '',
    birth_date: '',
});

const otpVerifyForm = useForm({
    phone_number: props.otpSentPhone || '',
    otp_code: '',
});

const selectRole = (roleId) => {
    selectedRole.value = roleId;
    form.selected_role = roleId;
    form.clearErrors();
    otpRequestForm.clearErrors();
    otpVerifyForm.clearErrors();
    if (route().has('role.login')) {
        window.history.replaceState({}, '', route('role.login', { role: roleId }));
    }
};

const submit = () => {
    form.selected_role = selectedRole.value;
    const postUrl = route().has('role.login.store')
        ? route('role.login.store', { role: selectedRole.value })
        : route('login');

    form.post(postUrl, {
        onFinish: () => form.reset('password'),
    });
};

const submitOtpRequest = () => {
    otpRequestForm.post(route('ortu.otp.request'), {
        preserveScroll: true,
        onSuccess: () => {
            otpVerifyForm.phone_number = otpRequestForm.phone_number;
            otpVerifyForm.clearErrors();
            otpStepRequested.value = true;
        },
    });
};

const submitOtpVerify = () => {
    otpVerifyForm.post(route('ortu.otp.verify'), {
        preserveScroll: true,
        onFinish: () => otpVerifyForm.reset('otp_code'),
    });
};

const resetOtpStep = () => {
    otpStepRequested.value = false;
    otpVerifyForm.reset('otp_code');
    otpVerifyForm.clearErrors();
};
</script>

<template>
    <Head :title="`Masuk ${currentRoleData.title} - SD Muhammadiyah Al Mujahidin Wonosari`" />

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-teal-50/50 text-slate-800 font-sans selection:bg-emerald-500 selection:text-white relative overflow-hidden flex flex-col justify-between">
        
        <!-- AMBIENT BRIGHT PASTEL GLOWS -->
        <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden">
            <div class="absolute -top-40 left-1/4 w-[650px] h-[550px] bg-emerald-200/50 rounded-full blur-[130px]"></div>
            <div class="absolute -bottom-40 right-1/4 w-[650px] h-[550px] bg-teal-200/40 rounded-full blur-[130px]"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[500px] bg-amber-100/60 rounded-full blur-[150px]"></div>
            <!-- Delicate grid pattern -->
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#0596690a_1px,transparent_1px),linear-gradient(to_bottom,#0596690a_1px,transparent_1px)] bg-[size:3rem_3rem]"></div>
        </div>

        <!-- TOP BAR / NAVIGATION -->
        <header class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex items-center justify-between">
            <Link 
                href="/" 
                class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-slate-700 hover:text-emerald-700 px-4 py-2 rounded-2xl bg-white/80 hover:bg-white border border-slate-200/80 shadow-sm backdrop-blur-md transition-all group"
            >
                <svg class="w-4 h-4 text-emerald-600 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Beranda</span>
            </Link>

            <div class="flex items-center gap-2 text-xs text-slate-600 bg-white/80 px-4 py-2 rounded-2xl border border-slate-200/80 shadow-sm backdrop-blur-md">
                <span class="hidden sm:inline">Belum punya akun?</span>
                <Link 
                    v-if="route().has('register')" 
                    :href="route('register')" 
                    class="font-bold text-emerald-700 hover:text-emerald-800 transition-colors flex items-center gap-1"
                >
                    <span>Daftar Inden / PPDB</span>
                    <span aria-hidden="true">→</span>
                </Link>
            </div>
        </header>

        <!-- MAIN LOGIN CONTENT (CENTERED DUAL-PANEL CARD) -->
        <main class="relative z-10 w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-3 sm:py-6 my-auto">
            
            <!-- ROLE SELECTOR TABS (PIL JALUR LOGIN) -->
            <div class="mb-4 bg-white/90 p-1.5 rounded-2xl border border-slate-200/90 shadow-md backdrop-blur-md flex flex-wrap items-center justify-between gap-1 max-w-3xl mx-auto">
                <button
                    v-for="role in roles"
                    :key="role.id"
                    type="button"
                    @click="selectRole(role.id)"
                    class="flex-1 py-2.5 px-3 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 text-center"
                    :class="currentRoleData.id === role.id ? role.activeTab : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'"
                >
                    <svg class="w-3.5 h-3.5 shrink-0 hidden sm:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="role.icon" />
                    </svg>
                    <span>{{ role.shortTitle }}</span>
                </button>
            </div>

            <div class="rounded-3xl bg-white border border-slate-200/90 shadow-2xl shadow-emerald-950/10 overflow-hidden grid grid-cols-1 lg:grid-cols-12 transition-all">
                
                <!-- LEFT BRANDING PANEL (COLORS ADAPT TO SELECTED ROLE) -->
                <div 
                    class="lg:col-span-5 p-8 lg:p-10 text-white flex flex-col justify-between relative overflow-hidden bg-gradient-to-br transition-all duration-500"
                    :class="currentRoleData.accentBg"
                >
                    <!-- Decorative subtle glow circles -->
                    <div class="absolute -top-24 -left-24 w-60 h-60 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                    <div class="absolute -bottom-24 -right-24 w-60 h-60 bg-amber-400/15 rounded-full blur-2xl pointer-events-none"></div>

                    <div class="relative z-10">
                        <!-- LOGO & BRAND -->
                        <div class="flex items-center gap-3.5 mb-8">
                            <div class="w-12 h-12 rounded-2xl bg-white/95 p-1 shadow-md shrink-0 flex items-center justify-center">
                                <!-- SURYA MUHAMMADIYAH ICON -->
                                <svg class="w-8 h-8 text-amber-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l1.5 3.5L17 4l-1 3.5 3.5 1-2.5 2.5 3 2.5-3.5 1 1 3.5-3.5-1-1.5 3.5-1.5-3.5L5 20l1-3.5-3.5-1 2.5-2.5-3-2.5 3.5-1-1-3.5 3.5 1L12 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base font-black text-white font-mono tracking-tight leading-tight">SD AL MUJAHIDIN</h2>
                                <p class="text-[11px] font-bold text-white/90 tracking-wide">WONOSARI • GUNUNGKIDUL</p>
                            </div>
                        </div>

                        <!-- BADGE & CURRENT PORTAL TITLE -->
                        <div class="inline-block px-3 py-1 rounded-full bg-white/15 border border-white/25 text-white text-xs font-semibold mb-3 backdrop-blur-sm">
                            {{ currentRoleData.badge }}
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight leading-tight mb-3">
                            {{ currentRoleData.portalName }}
                        </h1>
                        <p class="text-xs sm:text-sm text-white/90 leading-relaxed mb-6 font-normal">
                            {{ currentRoleData.desc }}
                        </p>

                        <!-- FEATURE BULLETS KHUSUS ROLE TERPILIH -->
                        <div class="space-y-3 text-xs text-white/90 pt-4 border-t border-white/15">
                            <div 
                                v-for="(feat, fIdx) in currentRoleData.features" 
                                :key="fIdx"
                                class="flex items-center gap-2.5"
                            >
                                <div class="w-5 h-5 rounded-lg bg-white/20 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span>{{ feat }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- ACCREDITATION BADGE -->
                    <div class="mt-8 pt-6 border-t border-white/15 text-[11px] text-white/80 flex items-center justify-between font-medium">
                        <span>NPSN: 20402170</span>
                        <span class="px-2.5 py-0.5 rounded-full bg-amber-400 text-slate-900 font-bold text-[10px] shadow-sm">
                            Akreditasi A Unggul
                        </span>
                    </div>
                </div>

                <!-- RIGHT FORM PANEL (BRIGHT, CLEAN & FOCUSED) -->
                <div class="lg:col-span-7 p-8 lg:p-12 bg-white flex flex-col justify-center">
                    
                    <div class="max-w-md w-full mx-auto">
                        <!-- FORM HEADER -->
                        <div class="mb-6">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold border mb-3" :class="currentRoleData.badgeBg">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                <span>Jalur Masuk: {{ currentRoleData.title }}</span>
                            </div>
                            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                                Masuk ke Portal
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-500 mt-1.5 font-normal">
                                Silakan gunakan akun terdaftar Anda untuk melanjutkan ke dashboard
                            </p>
                        </div>

                        <!-- STATUS FLASH MESSAGE -->
                        <div 
                            v-if="status" 
                            class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2"
                        >
                            <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ status }}</span>
                        </div>

                        <!-- TOGGLE METODE LOGIN KHUSUS ORANG TUA -->
                        <div
                            v-if="selectedRole === 'ortu'"
                            class="mb-5 p-1 rounded-xl bg-slate-100 border border-slate-200 grid grid-cols-2 gap-1"
                        >
                            <button
                                type="button"
                                @click="parentAuthMode = 'otp'"
                                class="py-2 px-3 rounded-lg text-xs font-bold transition-all"
                                :class="parentAuthMode === 'otp' ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                            >
                                OTP WhatsApp
                            </button>
                            <button
                                type="button"
                                @click="parentAuthMode = 'password'"
                                class="py-2 px-3 rounded-lg text-xs font-bold transition-all"
                                :class="parentAuthMode === 'password' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                            >
                                Email & Kata Sandi
                            </button>
                        </div>

                        <!-- FORM OTP WHATSAPP ORANG TUA (TANPA PASSWORD) -->
                        <div v-if="isParentOtpView" class="space-y-4">
                            <form v-if="!otpStepRequested" @submit.prevent="submitOtpRequest" class="space-y-4">
                                <div>
                                    <label for="otp_phone_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                        Nomor WhatsApp Orang Tua
                                    </label>
                                    <input
                                        id="otp_phone_number"
                                        type="tel"
                                        v-model="otpRequestForm.phone_number"
                                        required
                                        placeholder="081234567890"
                                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border text-sm text-slate-900 placeholder-slate-400 transition-all focus:bg-white focus:outline-none"
                                        :class="otpRequestForm.errors.phone_number
                                            ? 'border-rose-400 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10'
                                            : ['border-slate-200 focus:ring-4', currentRoleData.focusRing]"
                                    />
                                    <p v-if="otpRequestForm.errors.phone_number" class="mt-1.5 text-xs text-rose-600 font-medium">
                                        {{ otpRequestForm.errors.phone_number }}
                                    </p>
                                </div>

                                <div>
                                    <label for="otp_birth_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                        Tanggal Lahir Anak
                                    </label>
                                    <input
                                        id="otp_birth_date"
                                        type="date"
                                        v-model="otpRequestForm.birth_date"
                                        required
                                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border text-sm text-slate-900 transition-all focus:bg-white focus:outline-none"
                                        :class="otpRequestForm.errors.birth_date
                                            ? 'border-rose-400 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10'
                                            : ['border-slate-200 focus:ring-4', currentRoleData.focusRing]"
                                    />
                                    <p v-if="otpRequestForm.errors.birth_date" class="mt-1.5 text-xs text-rose-600 font-medium">
                                        {{ otpRequestForm.errors.birth_date }}
                                    </p>
                                    <p class="mt-1.5 text-[11px] text-slate-500">
                                        Cocok dengan data salah satu putra/putri Anda di sekolah.
                                    </p>
                                </div>

                                <div class="pt-2">
                                    <button
                                        type="submit"
                                        :disabled="otpRequestForm.processing"
                                        class="w-full py-3.5 px-6 rounded-xl font-bold text-sm text-white bg-gradient-to-r shadow-lg hover:scale-[1.01] active:scale-[0.99] transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                        :class="currentRoleData.btnBg"
                                    >
                                        <span>{{ otpRequestForm.processing ? 'Mengirim Kode OTP...' : 'Kirim Kode OTP ke WhatsApp' }}</span>
                                    </button>
                                </div>
                            </form>

                            <form v-else @submit.prevent="submitOtpVerify" class="space-y-4">
                                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-center justify-between gap-2">
                                    <div>
                                        <p class="font-bold">OTP dikirim ke {{ otpVerifyForm.phone_number }}</p>
                                        <p class="text-[11px] text-amber-700 mt-0.5">Berlaku selama 5 menit.</p>
                                    </div>
                                    <button
                                        type="button"
                                        @click="resetOtpStep"
                                        class="text-xs font-bold text-amber-800 underline hover:text-amber-950 shrink-0"
                                    >
                                        Ubah Nomor
                                    </button>
                                </div>

                                <div>
                                    <label for="otp_code" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                        Kode OTP 6 Digit
                                    </label>
                                    <input
                                        id="otp_code"
                                        type="text"
                                        inputmode="numeric"
                                        maxlength="6"
                                        v-model="otpVerifyForm.otp_code"
                                        required
                                        autofocus
                                        placeholder="Masukkan 6 digit kode"
                                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border text-center text-lg font-extrabold tracking-[0.35em] text-slate-900 placeholder:tracking-normal placeholder:text-sm placeholder:font-normal placeholder-slate-400 transition-all focus:bg-white focus:outline-none"
                                        :class="otpVerifyForm.errors.otp_code
                                            ? 'border-rose-400 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10'
                                            : ['border-slate-200 focus:ring-4', currentRoleData.focusRing]"
                                    />
                                    <p v-if="otpVerifyForm.errors.otp_code || otpVerifyForm.errors.phone_number" class="mt-1.5 text-xs text-rose-600 font-medium">
                                        {{ otpVerifyForm.errors.otp_code || otpVerifyForm.errors.phone_number }}
                                    </p>
                                </div>

                                <div class="pt-2 space-y-2.5">
                                    <button
                                        type="submit"
                                        :disabled="otpVerifyForm.processing"
                                        class="w-full py-3.5 px-6 rounded-xl font-bold text-sm text-white bg-gradient-to-r shadow-lg hover:scale-[1.01] active:scale-[0.99] transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                        :class="currentRoleData.btnBg"
                                    >
                                        <span>{{ otpVerifyForm.processing ? 'Memverifikasi OTP...' : 'Verifikasi & Masuk Portal' }}</span>
                                    </button>
                                    <button
                                        type="button"
                                        @click="submitOtpRequest"
                                        :disabled="otpRequestForm.processing || !otpRequestForm.birth_date"
                                        class="w-full py-2.5 px-4 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors disabled:opacity-50"
                                    >
                                        Kirim Ulang Kode OTP
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- LOGIN FORM (EMAIL & PASSWORD) -->
                        <form v-else @submit.prevent="submit" class="space-y-4">
                            
                            <!-- EMAIL INPUT -->
                            <div>
                                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                    Alamat Email / Identitas Akun
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                        </svg>
                                    </div>
                                    <input
                                        id="email"
                                        type="email"
                                        v-model="form.email"
                                        required
                                        autofocus
                                        autocomplete="username"
                                        :placeholder="currentRoleData.placeholder"
                                        class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-50 border text-sm text-slate-900 placeholder-slate-400 transition-all focus:bg-white focus:outline-none"
                                        :class="form.errors.email 
                                            ? 'border-rose-400 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10' 
                                            : ['border-slate-200 focus:ring-4', currentRoleData.focusRing]"
                                    />
                                </div>
                                <p v-if="form.errors.email" class="mt-1.5 text-xs text-rose-600 font-medium">
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <!-- PASSWORD INPUT -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                        Kata Sandi
                                    </label>
                                    <Link
                                        v-if="canResetPassword"
                                        :href="route('password.request')"
                                        class="text-xs text-emerald-700 hover:text-emerald-800 font-semibold transition-colors"
                                    >
                                        Lupa kata sandi?
                                    </Link>
                                </div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <input
                                        id="password"
                                        :type="showPassword ? 'text' : 'password'"
                                        v-model="form.password"
                                        required
                                        autocomplete="current-password"
                                        placeholder="••••••••"
                                        class="w-full pl-10 pr-11 py-3 rounded-xl bg-slate-50 border text-sm text-slate-900 placeholder-slate-400 transition-all focus:bg-white focus:outline-none"
                                        :class="form.errors.password 
                                            ? 'border-rose-400 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10' 
                                            : ['border-slate-200 focus:ring-4', currentRoleData.focusRing]"
                                    />
                                    <button 
                                        type="button" 
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                                        aria-label="Tampilkan atau sembunyikan kata sandi"
                                    >
                                        <!-- EYE OPEN -->
                                        <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <!-- EYE SLASH -->
                                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                        </svg>
                                    </button>
                                </div>
                                <p v-if="form.errors.password" class="mt-1.5 text-xs text-rose-600 font-medium">
                                    {{ form.errors.password }}
                                </p>
                            </div>

                            <!-- REMEMBER ME CHECKBOX -->
                            <div class="flex items-center justify-between pt-1">
                                <label class="flex items-center gap-2.5 cursor-pointer group select-none">
                                    <input 
                                        type="checkbox" 
                                        v-model="form.remember"
                                        class="w-4 h-4 rounded-md border-slate-300 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-2 transition-colors"
                                    />
                                    <span class="text-xs text-slate-600 group-hover:text-slate-900 transition-colors">
                                        Ingat saya di perangkat ini
                                    </span>
                                </label>
                            </div>

                            <!-- SUBMIT BUTTON -->
                            <div class="pt-2">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full py-3.5 px-6 rounded-xl font-bold text-sm text-white bg-gradient-to-r shadow-lg hover:scale-[1.01] active:scale-[0.99] transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :class="currentRoleData.btnBg"
                                >
                                    <svg 
                                        v-if="form.processing" 
                                        class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" 
                                        fill="none" 
                                        viewBox="0 0 24 24"
                                    >
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>{{ form.processing ? 'Memproses Masuk...' : `Masuk ke ${currentRoleData.title}` }}</span>
                                    <svg v-if="!form.processing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </button>
                            </div>

                        </form>

                        <!-- SECURITY BADGE FOOTER -->
                        <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-center gap-2 text-[11px] text-slate-400">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span>Koneksi aman dengan enkripsi SSL 256-bit SIMSE</span>
                        </div>

                    </div>

                </div>

            </div>
        </main>

        <!-- PAGE FOOTER -->
        <footer class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 text-center text-xs text-slate-500 font-medium">
            <p>© 2026 SD Muhammadiyah Al Mujahidin Wonosari. Seluruh hak cipta dilindungi.</p>
        </footer>

    </div>
</template>
