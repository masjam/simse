<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
});
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout>
        <template #header-title><h1 class="text-2xl font-black text-white">Dashboard Overview</h1></template>
        <template #header-subtitle><p class="text-slate-400">Selamat datang kembali, Super Admin!</p></template>

        <div class="space-y-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1 -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-6 relative overflow-hidden group">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-500/20 rounded-full blur-2xl group-hover:bg-blue-500/30 transition"></div>
                    <div class="flex items-center gap-4 relative z-10">
                        <div class="w-14 h-14 bg-blue-500/20 text-blue-400 rounded-xl flex items-center justify-center">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-slate-400 font-medium">Total Pengguna</p>
                            <h3 class="text-3xl font-black text-white mt-1">{{ stats.users_count }}</h3>
                        </div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-6 relative overflow-hidden group">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-500/20 rounded-full blur-2xl group-hover:bg-emerald-500/30 transition"></div>
                    <div class="flex items-center gap-4 relative z-10">
                        <div class="w-14 h-14 bg-emerald-500/20 text-emerald-400 rounded-xl flex items-center justify-center">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-slate-400 font-medium">Total Guru</p>
                            <h3 class="text-3xl font-black text-white mt-1">{{ stats.teachers_count }}</h3>
                        </div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-6 relative overflow-hidden group">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-rose-500/20 rounded-full blur-2xl group-hover:bg-rose-500/30 transition"></div>
                    <div class="flex items-center gap-4 relative z-10">
                        <div class="w-14 h-14 bg-rose-500/20 text-rose-400 rounded-xl flex items-center justify-center">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-slate-400 font-medium">Total Siswa</p>
                            <h3 class="text-3xl font-black text-white mt-1">{{ stats.students_count }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-slate-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Aksi Cepat</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <Link :href="route('admin.students.index')" class="p-4 bg-slate-900/50 hover:bg-white/5 rounded-xl border border-white/5 transition flex flex-col items-center justify-center gap-3 group text-center">
                        <div class="w-12 h-12 bg-white/5 rounded-full flex items-center justify-center text-slate-300 group-hover:text-blue-400 group-hover:scale-110 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        </div>
                        <div>
                            <p class="font-bold text-slate-200 text-sm">Tambah Pengguna</p>
                            <p class="text-[10px] text-slate-500">Buat akun baru</p>
                        </div>
                    </Link>
                    <div class="p-4 bg-slate-900/50 hover:bg-white/5 rounded-xl border border-white/5 transition flex flex-col items-center justify-center gap-3 group text-center cursor-pointer">
                        <div class="w-12 h-12 bg-white/5 rounded-full flex items-center justify-center text-slate-300 group-hover:text-emerald-400 group-hover:scale-110 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <p class="font-bold text-slate-200 text-sm">Tahun Ajaran</p>
                            <p class="text-[10px] text-slate-500">Atur periode akademik</p>
                        </div>
                    </div>
                    <div class="p-4 bg-slate-900/50 hover:bg-white/5 rounded-xl border border-white/5 transition flex flex-col items-center justify-center gap-3 group text-center cursor-pointer">
                        <div class="w-12 h-12 bg-white/5 rounded-full flex items-center justify-center text-slate-300 group-hover:text-amber-400 group-hover:scale-110 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                        </div>
                        <div>
                            <p class="font-bold text-slate-200 text-sm">Database Backup</p>
                            <p class="text-[10px] text-slate-500">Cadangkan data</p>
                        </div>
                    </div>
                    <Link :href="route('admin.settings')" class="p-4 bg-slate-900/50 hover:bg-white/5 rounded-xl border border-white/5 transition flex flex-col items-center justify-center gap-3 group text-center">
                        <div class="w-12 h-12 bg-white/5 rounded-full flex items-center justify-center text-slate-300 group-hover:text-rose-400 group-hover:scale-110 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                        </div>
                        <div>
                            <p class="font-bold text-slate-200 text-sm">Pengaturan Sistem</p>
                            <p class="text-[10px] text-slate-500">Konfigurasi web</p>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
