<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const page = usePage();
const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);

const user = computed(() => page.props.auth?.user || { name: 'Guru Demo', email: 'guru@simse.id' });

const currentRoute = computed(() => {
    try { return route().current(); } catch { return ''; }
});

const isActive = (routeName) => {
    try { return route().current(routeName) || route().current(routeName + '.*'); } catch { return false; }
};

const menuGroups = [
    {
        label: 'UTAMA',
        items: [
            { name: 'Dashboard', route: 'guru.dashboard', icon: 'home' },
            { name: 'Jadwal Mengajar', route: 'guru.jadwal', icon: 'calendar' },
        ]
    },
    {
        label: 'AKADEMIK',
        items: [
            { name: 'Absensi Kelas', route: 'guru.absensi', icon: 'clipboard-check' },
            { name: 'Input Nilai', route: 'guru.nilai', icon: 'chart-bar' },
            { name: 'e-Raport', route: 'guru.raport', icon: 'document-text' },
        ]
    },
    {
        label: 'PEMBELAJARAN',
        items: [
            { name: 'Materi Ajar (LMS)', route: 'guru.lms', icon: 'book-open' },
            { name: 'Tugas Siswa', route: 'guru.tugas', icon: 'pencil-alt' },
            { name: 'Bank Soal & CBT', route: 'guru.cbt', icon: 'puzzle' },
        ]
    },
    {
        label: 'LAYANAN',
        items: [
            { name: 'Bimbingan Konseling', route: 'guru.bk', icon: 'heart' },
            { name: 'Perpustakaan', route: 'guru.perpustakaan', icon: 'library' },
        ]
    },
    {
        label: 'KEUANGAN',
        items: [
            { name: 'Slip Gaji & Payroll', route: 'guru.payroll', icon: 'cash' },
        ]
    },
];

const getIcon = (icon) => {
    const icons = {
        'home': 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        'calendar': 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'clipboard-check': 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
        'chart-bar': 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        'document-text': 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'book-open': 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        'pencil-alt': 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        'puzzle': 'M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z',
        'heart': 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
        'library': 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z',
        'cash': 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
    };
    return icons[icon] || icons['home'];
};

const toggleSidebar = () => sidebarOpen.value = !sidebarOpen.value;
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex">
        <!-- Mobile Overlay -->
        <div v-if="sidebarOpen" @click="sidebarOpen = false"
             class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 lg:hidden transition-opacity"></div>

        <!-- Sidebar -->
        <aside :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'lg:w-20' : 'lg:w-72']"
               class="fixed inset-y-0 left-0 z-50 w-72 lg:translate-x-0 transition-all duration-300 ease-in-out flex flex-col">
            <!-- Sidebar Inner with Gradient -->
            <div class="flex flex-col h-full bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 overflow-hidden">
                <!-- Logo Area -->
                <div class="flex items-center justify-between h-16 px-4 border-b border-white/10 shrink-0">
                    <Link :href="route('guru.dashboard')" class="flex items-center gap-3 overflow-hidden">
                        <div class="w-9 h-9 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shrink-0 shadow-lg shadow-indigo-500/25">
                            <span class="text-white font-black text-sm">S</span>
                        </div>
                        <div v-if="!sidebarCollapsed" class="hidden lg:block">
                            <h1 class="text-white font-bold text-sm leading-tight">SIMSE</h1>
                            <p class="text-slate-400 text-[10px] font-medium">Portal Guru</p>
                        </div>
                    </Link>
                    <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden lg:flex w-7 h-7 items-center justify-center text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition">
                        <svg class="w-4 h-4 transition-transform" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
                    </button>
                    <button @click="sidebarOpen = false" class="lg:hidden w-7 h-7 flex items-center justify-center text-slate-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-6 sidebar-scroll">
                    <div v-for="group in menuGroups" :key="group.label">
                        <p v-if="!sidebarCollapsed" class="text-[10px] font-bold tracking-widest text-slate-500 uppercase mb-2 px-3">{{ group.label }}</p>
                        <div class="space-y-1">
                            <Link v-for="item in group.items" :key="item.route" :href="route(item.route)"
                                  class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 group"
                                  :class="isActive(item.route)
                                    ? 'bg-gradient-to-r from-indigo-600 to-indigo-500 text-white shadow-lg shadow-indigo-600/30'
                                    : 'text-slate-400 hover:text-white hover:bg-white/5'">
                                <div class="w-8 h-8 flex items-center justify-center rounded-lg shrink-0 transition-colors"
                                     :class="isActive(item.route) ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10'">
                                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIcon(item.icon)"></path></svg>
                                </div>
                                <span v-if="!sidebarCollapsed" class="truncate">{{ item.name }}</span>
                            </Link>
                        </div>
                    </div>
                </nav>

                <!-- User Info at Bottom -->
                <div class="border-t border-white/10 p-3 shrink-0">
                    <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-white/5 transition cursor-pointer">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white font-bold text-sm shrink-0 shadow-lg shadow-emerald-500/25">
                            {{ user.name?.charAt(0) || 'G' }}
                        </div>
                        <div v-if="!sidebarCollapsed" class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-white truncate">{{ user.name }}</p>
                            <p class="text-[11px] text-slate-400 truncate">{{ user.email }}</p>
                        </div>
                        <Link :href="route('logout')" method="post" as="button" v-if="!sidebarCollapsed"
                              class="text-slate-500 hover:text-red-400 transition shrink-0" title="Keluar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </Link>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-72'" class="flex-1 transition-all duration-300">
            <!-- Top Header Bar -->
            <header class="sticky top-0 z-30 h-16 bg-white/80 backdrop-blur-xl border-b border-slate-200/60 flex items-center justify-between px-4 sm:px-6">
                <div class="flex items-center gap-4">
                    <button @click="toggleSidebar" class="lg:hidden w-10 h-10 flex items-center justify-center text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <div>
                        <slot name="header-title">
                            <h1 class="text-lg font-bold text-slate-800">Portal Guru</h1>
                        </slot>
                        <slot name="header-subtitle"></slot>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Notification Bell -->
                    <button class="relative w-10 h-10 flex items-center justify-center text-slate-500 hover:bg-slate-100 rounded-xl transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                    </button>
                    <!-- Date Badge -->
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-slate-100 rounded-lg text-xs font-medium text-slate-600">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.sidebar-scroll::-webkit-scrollbar {
    width: 4px;
}
.sidebar-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.sidebar-scroll::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
}
.sidebar-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.2);
}
</style>
