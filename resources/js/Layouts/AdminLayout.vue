<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import NotificationBell from '@/Components/NotificationBell.vue';

const page = usePage();
const user = page.props.auth.user;
const isSidebarOpen = ref(true);
const openMenu = ref('Manajemen Pengguna'); // keep it open by default for visibility

const menuItems = [
    { name: 'Dashboard Utama', route: 'admin.dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { 
        name: 'Manajemen Pengguna', 
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        subItems: [
            { name: 'Data Siswa', route: 'admin.students.index' },
            { name: 'Data Guru', route: 'admin.teachers.index' },
            { name: 'Data Staf', route: 'admin.staffs.index' }
        ]
    },
    { name: 'Manajemen Modul (Microservices)', route: 'admin.modules', icon: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z' },
    
    // Kendali Modul Spesifik
    { name: 'Data Induk & KTS', route: 'admin.settings', icon: 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2' },
    { name: 'Tingkat', route: 'admin.tingkat.index', icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' },
    { name: 'Rombel', route: 'admin.student-classes.index', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
    { name: 'Mata Pelajaran', route: 'admin.subjects.index', icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' },
    { name: 'Pembagian Tugas', route: 'admin.teaching-assignments.index', icon: 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z' },
    { name: 'Jadwal Pelajaran', route: 'admin.schedules.index', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
    { name: 'Verifikasi Prestasi', route: 'achievements.verification.index', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { name: 'PPDB Siswa Baru', route: 'admin.ppdb.index', icon: 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z' },
    { name: 'Keuangan & Payroll', route: 'admin.settings', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { name: 'CBT & Akademik', route: 'admin.settings', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
    { name: 'WA Gateway', route: 'sistem.wa', icon: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z' },
    { name: 'Perangkat Kiosk (MAC)', route: 'sistem.kiosk-devices', icon: 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
    { name: 'CMS & Profil Sekolah', route: 'sistem.website', icon: 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9' },
    
    { name: 'Pengaturan Sistem', route: 'sistem.pengaturan', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' },
];

const toggleMenu = (menuName) => {
    if (openMenu.value === menuName) {
        openMenu.value = null;
    } else {
        openMenu.value = menuName;
    }
};

const isMenuActive = (item) => {
    if (item.route && route().current(item.route)) return true;
    if (item.subItems) {
        return item.subItems.some(sub => route().current(sub.route));
    }
    return false;
};

</script>

<template>
    <div class="min-h-screen bg-slate-900 flex text-slate-100 font-sans selection:bg-rose-500 selection:text-white">
        <!-- Sidebar -->
        <aside :class="['bg-slate-950/50 backdrop-blur-xl border-r border-white/5 transition-all duration-300 flex flex-col relative z-20', isSidebarOpen ? 'w-72' : 'w-20']">
            <!-- Logo -->
            <div class="h-20 flex items-center px-6 border-b border-white/5 shrink-0">
                <div class="w-10 h-10 bg-gradient-to-br from-rose-500 to-orange-400 rounded-xl flex items-center justify-center shadow-lg shadow-rose-500/20 shrink-0 relative overflow-hidden group cursor-pointer">
                    <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform"></div>
                    <span class="text-white font-black text-xl tracking-tighter relative z-10">S.</span>
                </div>
                <div v-if="isSidebarOpen" class="ml-4 truncate">
                    <h1 class="font-bold text-white tracking-tight leading-tight">SIMSE</h1>
                    <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Super Admin</p>
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex-1 overflow-y-auto py-6 px-4 space-y-2 custom-scrollbar">
                <div v-for="item in menuItems" :key="item.name">
                    <!-- Menu Item without Submenu -->
                    <Link v-if="!item.subItems" :href="route(item.route)" 
                          class="flex items-center gap-4 px-3 py-3 rounded-xl transition-all duration-200 group relative"
                          :class="isMenuActive(item) ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white'">
                        
                        <div v-if="isMenuActive(item)" class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-rose-500 rounded-r-full shadow-[0_0_10px_rgba(244,63,94,0.5)]"></div>
                        
                        <svg class="w-5 h-5 shrink-0 transition-transform group-hover:scale-110" :class="isMenuActive(item) ? 'text-rose-400' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                        </svg>
                        <span v-if="isSidebarOpen" class="font-medium text-sm truncate">{{ item.name }}</span>
                        
                        <div v-if="!isSidebarOpen" class="absolute left-full ml-4 px-3 py-1.5 bg-slate-800 text-white text-xs font-bold rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all whitespace-nowrap z-50 shadow-xl border border-white/10">
                            {{ item.name }}
                        </div>
                    </Link>

                    <!-- Menu Item with Submenu -->
                    <div v-else>
                        <button @click="toggleMenu(item.name)" 
                              class="w-full flex items-center justify-between px-3 py-3 rounded-xl transition-all duration-200 group relative"
                              :class="isMenuActive(item) ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white'">
                            
                            <div v-if="isMenuActive(item)" class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-rose-500 rounded-r-full shadow-[0_0_10px_rgba(244,63,94,0.5)]"></div>
                            
                            <div class="flex items-center gap-4">
                                <svg class="w-5 h-5 shrink-0 transition-transform group-hover:scale-110" :class="isMenuActive(item) ? 'text-rose-400' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                                </svg>
                                <span v-if="isSidebarOpen" class="font-medium text-sm truncate">{{ item.name }}</span>
                            </div>

                            <svg v-if="isSidebarOpen" class="w-4 h-4 transition-transform" :class="openMenu === item.name ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                            
                            <div v-if="!isSidebarOpen" class="absolute left-full ml-4 px-3 py-1.5 bg-slate-800 text-white text-xs font-bold rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all whitespace-nowrap z-50 shadow-xl border border-white/10">
                                {{ item.name }}
                            </div>
                        </button>

                        <!-- Submenu -->
                        <div v-if="isSidebarOpen && openMenu === item.name" class="pl-11 pr-3 py-2 space-y-1">
                            <Link v-for="sub in item.subItems" :key="sub.name" :href="route(sub.route)"
                                class="block px-3 py-2 rounded-lg text-sm transition-all"
                                :class="route().current(sub.route) ? 'bg-white/5 text-rose-400 font-bold' : 'text-slate-400 hover:text-white hover:bg-white/5'">
                                {{ sub.name }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Widget -->
            <div class="p-4 border-t border-white/5">
                <Link :href="route('profile.edit')" class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/5 transition cursor-pointer">
                    <img :src="`https://ui-avatars.com/api/?name=${user.name}&background=f43f5e&color=fff`" class="w-10 h-10 rounded-full border-2 border-slate-800 shrink-0" />
                    <div v-if="isSidebarOpen" class="truncate">
                        <p class="text-sm font-bold text-white truncate">{{ user.name }}</p>
                        <p class="text-[11px] text-slate-500 truncate">{{ user.email }}</p>
                    </div>
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] bg-fixed relative">
            <div class="absolute inset-0 bg-slate-900/95 -z-10"></div>
            <!-- Topbar -->
            <header class="h-20 bg-slate-950/30 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 sticky top-0 z-10">
                <div class="flex items-center gap-4">
                    <button @click="isSidebarOpen = !isSidebarOpen" class="w-10 h-10 rounded-xl bg-white/5 hover:bg-white/10 flex items-center justify-center text-slate-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                    </button>
                    <div>
                        <slot name="header-title"><h2 class="text-xl font-bold text-white">Administrator</h2></slot>
                        <slot name="header-subtitle"><p class="text-xs text-slate-400">Pusat Kendali Sistem Informasi Sekolah</p></slot>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <NotificationBell theme="dark" />
                    
                    <Link :href="route('logout')" method="post" as="button" class="px-4 py-2 bg-white/5 hover:bg-rose-500 hover:text-white text-slate-300 text-sm font-bold rounded-lg transition-all border border-white/5 hover:border-rose-500">
                        Logout
                    </Link>
                </div>
            </header>

            <!-- Page Content -->
            <div class="flex-1 p-8 overflow-y-auto">
                <div class="max-w-7xl mx-auto">
                    <slot />
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}
</style>
