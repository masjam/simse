<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import NotificationBell from '@/Components/NotificationBell.vue';

const page = usePage();
const sidebarOpen = ref(false);

const user = computed(() => page.props.auth?.user || { name: 'Siswa', email: 'siswa@sekolah.com' });
const flash = computed(() => page.props.flash || {});
const activeModules = computed(() => page.props.activeModules || {});
const isModuleActive = (code) => !code || activeModules.value[code] !== false;

const isActive = (routeName) => {
    try {
        return route().current(routeName) || route().current(`${routeName}.*`);
    } catch {
        return false;
    }
};

const menuGroups = [
    {
        label: 'UTAMA',
        items: [
            { name: 'Dashboard Siswa', route: 'siswa.dashboard', icon: 'home' },
            { name: 'Jadwal Pelajaran', route: 'siswa.jadwal', icon: 'calendar' },
            { name: 'Presensi Saya', route: 'siswa.presensi', module: 'absensi', icon: 'check' },
        ],
    },
    {
        label: 'BELAJAR & UJIAN',
        items: [
            { name: 'Materi & Tugas (LMS)', route: 'siswa.lms', module: 'lms', icon: 'book' },
            { name: 'Materi Mingguan', route: 'siswa.materi-mingguan', module: 'lms', icon: 'exam' },
            { name: 'Ujian Online (CBT)', route: 'siswa.cbt', module: 'cbt', icon: 'exam' },
            { name: 'Nilai & e-Raport', route: 'siswa.nilai', icon: 'chart' },
        ],
    },
    {
        label: 'IDENTITAS & LAYANAN',
        items: [
            { name: 'e-Kantin Digital', route: 'siswa.kantin', module: 'kantin', icon: 'book' },
            { name: 'KTS Digital', route: 'siswa.kts', icon: 'card' },
        ],
    },
];

const mobileNav = [
    { name: 'Beranda', route: 'siswa.dashboard', icon: 'home' },
    { name: 'Jadwal', route: 'siswa.jadwal', icon: 'calendar' },
    { name: 'LMS', route: 'siswa.lms', module: 'lms', icon: 'book' },
    { name: 'CBT', route: 'siswa.cbt', module: 'cbt', icon: 'exam' },
    { name: 'Nilai', route: 'siswa.nilai', icon: 'chart' },
];

const filteredMenuGroups = computed(() =>
    menuGroups
        .map((group) => ({
            ...group,
            items: group.items.filter((item) => isModuleActive(item.module)),
        }))
        .filter((group) => group.items.length > 0)
);

const filteredMobileNav = computed(() =>
    mobileNav.filter((item) => isModuleActive(item.module))
);

const icons = {
    home: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    calendar: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    check: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
    book: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
    exam: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    chart: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    card: 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2',
};

const getIcon = (name) => icons[name] || icons.home;
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex flex-col lg:flex-row text-slate-800">
        <!-- Mobile Sidebar Backdrop -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <!-- Sidebar -->
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-72 bg-gradient-to-b from-sky-900 via-teal-900 to-emerald-950 text-white lg:translate-x-0 lg:static lg:shrink-0 transition-transform duration-300 flex flex-col print:hidden"
        >
            <div class="h-16 px-5 border-b border-white/15 flex items-center justify-between">
                <Link :href="route('siswa.dashboard')" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-emerald-400 flex items-center justify-center shadow-lg font-black text-slate-900">
                        S
                    </div>
                    <div>
                        <p class="font-extrabold text-sm tracking-wide leading-tight">SIMSE</p>
                        <p class="text-[11px] text-sky-200 font-medium">Portal Siswa</p>
                    </div>
                </Link>
                <button
                    type="button"
                    class="lg:hidden p-2 rounded-lg text-white/80 hover:bg-white/10"
                    @click="sidebarOpen = false"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto px-4 py-5 space-y-6">
                <div v-for="group in filteredMenuGroups" :key="group.label">
                    <p class="px-3 text-[10px] font-bold uppercase tracking-widest text-sky-200/70 mb-2">
                        {{ group.label }}
                    </p>
                    <div class="space-y-1">
                        <Link
                            v-for="item in group.items"
                            :key="item.route"
                            :href="route(item.route)"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all"
                            :class="isActive(item.route)
                                ? 'bg-white text-teal-900 shadow-md'
                                : 'text-sky-100 hover:bg-white/10 hover:text-white'"
                            @click="sidebarOpen = false"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIcon(item.icon)" />
                            </svg>
                            <span>{{ item.name }}</span>
                        </Link>
                    </div>
                </div>
            </nav>

            <div class="p-4 border-t border-white/15 bg-black/15">
                <div class="flex items-center gap-3 mb-3">
                    <img
                        :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=0ea5e9&color=ffffff`"
                        alt="Avatar Siswa"
                        class="w-10 h-10 rounded-full border-2 border-white/40 shrink-0"
                    />
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-white truncate">{{ user.name }}</p>
                        <p class="text-[11px] text-sky-200 truncate">{{ user.email }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <Link
                        :href="route('profile.edit')"
                        class="text-center text-xs font-semibold py-2 px-2 rounded-lg bg-white/10 hover:bg-white/20 text-white transition"
                    >
                        Profil
                    </Link>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-center text-xs font-semibold py-2 px-2 rounded-lg bg-rose-500/80 hover:bg-rose-500 text-white transition"
                    >
                        Keluar
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Main Area -->
        <div class="flex-1 flex flex-col min-w-0 pb-20 lg:pb-0">
            <!-- Topbar -->
            <header class="h-16 bg-white border-b border-slate-200 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-30 shadow-sm print:hidden">
                <div class="flex items-center gap-3 min-w-0">
                    <button
                        type="button"
                        class="lg:hidden p-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200"
                        @click="sidebarOpen = true"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div class="min-w-0">
                        <slot name="header">
                            <h1 class="text-base sm:text-lg font-extrabold text-slate-800 truncate">Portal Siswa</h1>
                        </slot>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <NotificationBell />
                    <Link
                        :href="route('siswa.kts')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-sky-50 text-sky-700 border border-sky-200 text-xs font-bold hover:bg-sky-100 transition"
                    >
                        <span>KTS Digital</span>
                    </Link>
                </div>
            </header>

            <!-- Flash Messages -->
            <div v-if="flash.success || flash.error" class="px-4 sm:px-6 pt-4 print:hidden">
                <div
                    v-if="flash.success"
                    class="max-w-6xl mx-auto p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-2"
                >
                    <span>{{ flash.success }}</span>
                </div>
                <div
                    v-if="flash.error"
                    class="max-w-6xl mx-auto mt-2 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center gap-2"
                >
                    <span>{{ flash.error }}</span>
                </div>
            </div>

            <main class="flex-1 p-4 sm:p-6">
                <div class="max-w-6xl mx-auto">
                    <slot />
                </div>
            </main>
        </div>

        <!-- Mobile Bottom Navigation -->
        <nav
            class="fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 px-2 py-1.5 grid gap-1 z-30 lg:hidden shadow-lg print:hidden"
            :style="{ gridTemplateColumns: `repeat(${Math.max(filteredMobileNav.length, 1)}, minmax(0, 1fr))` }"
        >
            <Link
                v-for="item in filteredMobileNav"
                :key="item.route"
                :href="route(item.route)"
                class="flex flex-col items-center justify-center py-1.5 rounded-xl text-[11px] font-bold transition"
                :class="isActive(item.route) ? 'text-teal-700 bg-teal-50' : 'text-slate-500'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIcon(item.icon)" />
                </svg>
                <span>{{ item.name }}</span>
            </Link>
        </nav>
    </div>
</template>

