<script setup>
import StaffLayout from '@/Layouts/StaffLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: { type: Object, required: true },
    recentAttendances: { type: Array, required: true },
});

const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
}).format(amount || 0);
</script>

<template>
    <Head title="Dashboard Staf" />
    <StaffLayout>
        <template #header-title>
            <h1 class="text-2xl font-black text-slate-800">Dashboard Staf</h1>
        </template>
        <template #header-subtitle>
            <p class="text-slate-500 font-medium">Ringkasan aktivitas operasional, presensi GPS, dan perpustakaan</p>
        </template>

        <div class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Status Kehadiran Hari Ini -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-2">Presensi Hari Ini</p>
                    <div class="flex items-center gap-2">
                        <span
                            class="w-3 h-3 rounded-full"
                            :class="stats.today_status === 'Hadir' ? 'bg-emerald-500 animate-pulse' : (stats.today_status === 'Terlambat' ? 'bg-amber-500' : 'bg-slate-300')"
                        ></span>
                        <h3 class="text-lg font-black text-slate-800">{{ stats.today_status }}</h3>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">
                        Masuk: <strong>{{ stats.time_in }}</strong> &middot; Pulang: <strong>{{ stats.time_out }}</strong>
                    </p>
                    <Link :href="route('staf.absensi')" class="text-xs font-bold text-indigo-600 hover:underline mt-2 inline-block">
                        Buka Presensi GPS &rarr;
                    </Link>
                </div>

                <!-- Total Hari Hadir Bulan Ini -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-2">Kehadiran Bulan Ini</p>
                    <h3 class="text-2xl font-black text-emerald-700">{{ stats.present_days }} Hari</h3>
                    <p class="text-xs text-slate-500 mt-1">Terhubung langsung ke tunjangan payroll</p>
                </div>

                <!-- Perpustakaan -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-2">Sirkulasi Perpustakaan</p>
                    <h3 class="text-2xl font-black text-indigo-700">{{ stats.active_loans }} Dipinjam</h3>
                    <p class="text-xs text-rose-600 font-bold mt-1" v-if="stats.overdue_loans > 0">
                        {{ stats.overdue_loans }} peminjaman melewati jatuh tempo
                    </p>
                    <p class="text-xs text-slate-500 mt-1" v-else>
                        Semua peminjaman tepat waktu
                    </p>
                </div>

                <!-- Slip Gaji -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-2">Slip Gaji Terakhir</p>
                    <h3 class="text-2xl font-black text-slate-800">{{ formatRupiah(stats.latest_salary) }}</h3>
                    <Link :href="route('staf.gaji')" class="text-xs font-bold text-indigo-600 hover:underline mt-2 inline-block">
                        Rincian Slip Gaji &rarr;
                    </Link>
                </div>
            </div>

            <!-- Riwayat Presensi Singkat -->
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-extrabold text-slate-800">Riwayat Presensi Terbaru</h2>
                    <Link :href="route('staf.absensi')" class="text-xs font-bold text-indigo-600 hover:underline">
                        Lihat Selengkapnya &rarr;
                    </Link>
                </div>
                <div v-if="recentAttendances.length === 0" class="text-center py-6 text-sm text-slate-400">
                    Belum ada catatan presensi bulan ini.
                </div>
                <div v-else class="divide-y divide-slate-100">
                    <div v-for="att in recentAttendances" :key="att.id" class="py-3 flex items-center justify-between text-xs">
                        <div>
                            <p class="font-bold text-slate-800">{{ att.date }}</p>
                            <p class="text-slate-500">Masuk: {{ att.time_in }} &middot; Pulang: {{ att.time_out }}</p>
                        </div>
                        <span
                            class="px-2.5 py-1 rounded-full font-bold"
                            :class="att.status === 'Hadir' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                        >
                            {{ att.status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </StaffLayout>
</template>
