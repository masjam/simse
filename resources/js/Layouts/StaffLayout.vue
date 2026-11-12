<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const page = usePage();
const user = page.props.auth.user;
const isSidebarOpen = ref(true);

const menuItems = [
    { name: 'Dashboard Staf', route: 'staf.dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { name: 'Absensi Kehadiran', route: 'staf.absensi', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    { name: 'Katalog Perpustakaan', route: 'staf.perpustakaan', icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' },
    { name: 'Slip Gaji Saya', route: 'staf.gaji', icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z' },
];

</script>

<template>
    <div class="min-h-screen bg-slate-50 flex text-slate-800 font-sans selection:bg-indigo-500 selection:text-white">
        <!-- Sidebar -->
        <aside :class="['bg-white border-r border-slate-200 transition-all duration-300 flex flex-col relative z-20 shadow-xl shadow-slate-200/50', isSidebarOpen ? 'w-72' : 'w-20']">
            <!-- Logo -->
            <div class="h-20 flex items-center px-6 border-b border-slate-100 shrink-0">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-blue-400 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20 shrink-0 relative overflow-hidden group cursor-pointer">
                    <span class="text-white font-black text-xl tracking-tighter relative z-10">St.</span>
                </div>
                <div v-if="isSidebarOpen" class="ml-4 truncate">
                    <h1 class="font-black text-slate-800 tracking-tight leading-tight">SIMSE</h1>
                    <p class="text-[10px] uppercase tracking-widest text-indigo-600 font-bold">Staf Sekolah</p>
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex-1 overflow-y-auto py-6 px-4 space-y-2 custom-scrollbar">
                <Link v-for="item in menuItems" :key="item.name" :href="route(item.route)" 
                      class="flex items-center gap-4 px-3 py-3 rounded-xl transition-all duration-200 group relative"
                      :class="route().current(item.route) ? 'bg-indigo-50 text-indigo-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800'">
                    
                    <div v-if="route().current(item.route)" class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-indigo-500 rounded-r-full"></div>
                    
                    <svg class="w-5 h-5 shrink-0 transition-transform group-hover:scale-110" :class="route().current(item.route) ? 'text-indigo-500' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                    </svg>
                    <span v-if="isSidebarOpen" class="font-bold text-sm truncate">{{ item.name }}</span>
                </Link>
            </div>

            <!-- Profile Widget -->
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                <Link :href="route('profile.edit')" class="flex items-center gap-3 p-3 rounded-xl hover:bg-white transition cursor-pointer border border-transparent hover:border-slate-200 shadow-sm">
                    <img :src="`https://ui-avatars.com/api/?name=${user.name}&background=6366f1&color=fff`" class="w-10 h-10 rounded-full border-2 border-white shrink-0 shadow-sm" />
                    <div v-if="isSidebarOpen" class="truncate">
                        <p class="text-sm font-bold text-slate-800 truncate">{{ user.name }}</p>
                        <p class="text-[11px] text-slate-500 truncate font-medium">{{ user.email }}</p>
                    </div>
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] bg-fixed relative">
            <div class="absolute inset-0 bg-slate-50/90 -z-10 backdrop-blur-3xl"></div>
            <!-- Topbar -->
            <header class="h-20 bg-white/80 backdrop-blur-md border-b border-slate-200 flex items-center justify-between px-8 sticky top-0 z-10 shadow-sm">
                <div class="flex items-center gap-4">
                    <button @click="isSidebarOpen = !isSidebarOpen" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                    </button>
                    <div>
                        <slot name="header-title"><h2 class="text-xl font-black text-slate-800">Layanan Staf</h2></slot>
                        <slot name="header-subtitle"><p class="text-xs font-medium text-slate-500">Akses pendukung layanan sekolah</p></slot>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button class="relative p-2 text-slate-400 hover:text-indigo-500 transition">
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 rounded-full"></span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </button>
                    
                    <Link :href="route('logout')" method="post" as="button" class="px-4 py-2 bg-white hover:bg-slate-800 hover:text-white text-slate-600 text-sm font-bold rounded-lg transition-all border border-slate-200 shadow-sm">
                        Keluar
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
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
