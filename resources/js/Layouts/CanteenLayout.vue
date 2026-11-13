<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import NotificationBell from '@/Components/NotificationBell.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user || { name: 'Pengelola Kantin', email: 'kantin@sekolah.com' });
const flash = computed(() => page.props.flash || {});
const sidebarOpen = ref(false);

const menuItems = [
    { name: 'Dashboard & Pesanan', route: 'kantin.dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { name: 'Kasir ATM (RFID)', route: 'kantin.rfid', icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' },
    { name: 'Kelola Menu & Stok', route: 'kantin.menus', icon: 'M12 6v6m0 0v6m0-6h6m-6 0H6' },
    { name: 'Scan QR Ambil Pesanan', route: 'kantin.scan', icon: 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z' },
];
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex flex-col lg:flex-row text-slate-800">
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-72 bg-gradient-to-b from-amber-950 via-orange-950 to-slate-950 text-white lg:translate-x-0 lg:static lg:shrink-0 transition-transform duration-300 flex flex-col"
        >
            <div class="h-16 px-5 border-b border-white/10 flex items-center justify-between">
                <Link :href="route('kantin.dashboard')" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg font-black text-slate-900">
                        K
                    </div>
                    <div>
                        <p class="font-extrabold text-sm tracking-wide leading-tight">SIMSE</p>
                        <p class="text-[11px] text-amber-200 font-medium">Portal e-Kantin</p>
                    </div>
                </Link>
                <button class="lg:hidden text-amber-200 hover:text-white" @click="sidebarOpen = false">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto px-4 py-5 space-y-1.5">
                <Link
                    v-for="item in menuItems"
                    :key="item.route"
                    :href="route(item.route)"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all"
                    :class="route().current(item.route) ? 'bg-amber-400 text-slate-950 shadow-md' : 'text-amber-100/80 hover:bg-white/10 hover:text-white'"
                    @click="sidebarOpen = false"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                    </svg>
                    <span>{{ item.name }}</span>
                </Link>
            </nav>

            <div class="p-4 border-t border-white/10 bg-black/20">
                <div class="flex items-center gap-3 mb-3">
                    <img
                        :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=f59e0b&color=0f172a`"
                        alt="Avatar Kantin"
                        class="w-10 h-10 rounded-full border-2 border-amber-300/50 shrink-0"
                    />
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-white truncate">{{ user.name }}</p>
                        <p class="text-[11px] text-amber-200 truncate">{{ user.email }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="route('profile.edit')"
                        class="flex-1 text-center py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold text-white transition"
                    >
                        Profil
                    </Link>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="flex-1 text-center py-1.5 rounded-lg bg-rose-500/20 hover:bg-rose-500/30 text-xs font-semibold text-rose-200 transition"
                    >
                        Keluar
                    </Link>
                </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-16 bg-white border-b border-slate-200 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center gap-3 min-w-0">
                    <button
                        type="button"
                        class="lg:hidden p-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100"
                        @click="sidebarOpen = true"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div class="min-w-0">
                        <slot name="header">
                            <h1 class="text-base sm:text-lg font-extrabold text-slate-800 truncate">Portal Pengelola Stand Kantin</h1>
                        </slot>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <NotificationBell />
                </div>
            </header>

            <div v-if="flash.success || flash.error" class="px-4 sm:px-6 pt-4">
                <div v-if="flash.success" class="max-w-6xl mx-auto p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold">
                    {{ flash.success }}
                </div>
                <div v-if="flash.error" class="max-w-6xl mx-auto mt-2 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold">
                    {{ flash.error }}
                </div>
            </div>

            <main class="flex-1 p-4 sm:p-6">
                <div class="max-w-6xl mx-auto">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

