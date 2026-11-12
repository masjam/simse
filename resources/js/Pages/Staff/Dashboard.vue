<script setup>
import StaffLayout from '@/Layouts/StaffLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard Staf" />
    <StaffLayout>
        <template #header-title><h1 class="text-2xl font-black text-slate-800">Dashboard Staf</h1></template>
        <template #header-subtitle><p class="text-slate-500 font-medium">Ringkasan aktivitas hari ini</p></template>

        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Status Kehadiran -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex items-start justify-between">
                    <div>
                        <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-2">Status Kehadiran Hari Ini</p>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></div>
                            <h3 class="text-xl font-black text-slate-800">Hadir (07:15)</h3>
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>

                <!-- Info Slip Gaji -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex items-start justify-between">
                    <div>
                        <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-2">Slip Gaji Terakhir</p>
                        <h3 class="text-xl font-black text-slate-800">September 2026</h3>
                        <Link :href="route('staf.gaji')" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 mt-1 inline-block">Lihat Detail &rarr;</Link>
                    </div>
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>

                <!-- Tugas Cepat -->
                <div class="bg-gradient-to-br from-indigo-500 to-blue-600 rounded-2xl p-6 shadow-lg shadow-indigo-500/30 text-white flex flex-col justify-between">
                    <div>
                        <h3 class="text-lg font-black mb-1">Katalog Perpustakaan</h3>
                        <p class="text-indigo-100 text-sm">Kelola data buku atau bantu peminjaman siswa.</p>
                    </div>
                    <Link :href="route('staf.perpustakaan')" class="mt-4 px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-bold w-max transition">
                        Buka Katalog
                    </Link>
                </div>
            </div>
        </div>
    </StaffLayout>
</template>
