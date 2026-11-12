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
        title: 'Siswa & Wali Santri',
        shortTitle: 'Siswa & Wali',
        badge: 'Peserta Didik & Orang Tua',
        portalName: 'Portal Siswa & Orang Tua',
        desc: 'Pantau presensi harian, nilai e-Raport, mutaba\'ah tahfidz, dan saldo jajan e-Kantin.',
        placeholder: 'nisn.siswa@sdmujahidin-wns.sch.id / NISN',
        accentBg: 'from-sky-600 via-blue-700 to-indigo-800',
        badgeBg: 'bg-sky-50 text-sky-700 border-sky-200',
        btnBg: 'from-sky-600 via-blue-600 to-indigo-600 hover:from-sky-500 hover:to-blue-500 shadow-sky-700/20',
        focusRing: 'focus:border-sky-600 focus:ring-sky-500/10',
        activeTab: 'bg-sky-600 text-white shadow-md shadow-sky-600/30',
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        features: [
            'KTS Digital Ber-QR Code untuk Presensi & Perpus',
            'Notifikasi Kehadiran Otomatis ke WhatsApp',
            'Monitoring Saldo & Riwayat Jajan Sehat e-Kantin',
            'Capaian Mutaba\'ah Hafalan Tahfidz Qur\'an'
        ],
    },
    {
        id: 'staf',
        title: 'Staf & Keuangan',
        shortTitle: 'Staf & TU',
        badge: 'Tata Usaha & Bendahara',
        portalName: 'Portal Keuangan & Administrasi',
        desc: 'Pengelolaan tagihan SPP siswa, kasir kas sekolah, sirkulasi buku perpus, dan payroll staf.',
        placeholder: 'keuangan@sdmujahidin-wns.sch.id',
        accentBg: 'from-amber-600 via-orange-600 to-amber-700',
        badgeBg: 'bg-amber-50 text-amber-700 border-amber-200',
        btnBg: 'from-amber-600 via-orange-600 to-amber-600 hover:from-amber-500 hover:to-orange-500 shadow-amber-700/20',
        focusRing: 'focus:border-amber-600 focus:ring-amber-500/10',
        activeTab: 'bg-amber-600 text-white shadow-md shadow-amber-600/30',
        icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
        features: [
            'Pembukuan Tagihan SPP & Invoice Otomatis',
            'Point of Sales (POS) Kasir Kas Sekolah',
            'Layanan Sirkulasi Peminjaman Perpustakaan',
            'Kalkulasi Honor Jam Mengajar & Slip Gaji'
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

onMounted(() => {
    // Ambil parameter ?role= dari URL jika ada
    const urlParams = new URLSearchParams(window.location.search);
    const roleParam = urlParams.get('role');
    if (roleParam && roles.some(r => r.id === roleParam)) {
        selectedRole.value = roleParam;
    }
});

const currentRoleData = computed(() => {
    return roles.find(r => r.id === selectedRole.value) || roles[0];
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
    selected_role: selectedRole.value,
});

const selectRole = (roleId) => {
    selectedRole.value = roleId;
    form.selected_role = roleId;
    form.clearErrors();
};

const submit = () => {
    form.selected_role = selectedRole.value;
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
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
            <div class="mb-4 bg-white/90 p-1.5 rounded-2xl border border-slate-200/90 shadow-md backdrop-blur-md flex items-center justify-between gap-1 max-w-2xl mx-auto">
                <button
                    v-for="role in roles"
                    :key="role.id"
                    type="button"
                    @click="selectRole(role.id)"
                    class="flex-1 py-2.5 px-3 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 text-center"
                    :class="selectedRole === role.id ? role.activeTab : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'"
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

                        <!-- LOGIN FORM -->
                        <form @submit.prevent="submit" class="space-y-4">
                            
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
