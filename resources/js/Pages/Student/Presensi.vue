<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    student: { type: Object, required: true },
    summary: { type: Object, required: true },
    attendances: { type: Array, default: () => [] },
});

const badgeClass = (status) => {
    switch (status) {
        case 'Hadir':
            return 'bg-emerald-100 text-emerald-700';
        case 'Terlambat':
            return 'bg-amber-100 text-amber-800';
        case 'Izin':
            return 'bg-sky-100 text-sky-700';
        case 'Sakit':
            return 'bg-indigo-100 text-indigo-700';
        default:
            return 'bg-rose-100 text-rose-700';
    }
};
</script>

<template>
    <Head title="Presensi Saya" />

    <StudentLayout>
        <template #header>
            <div>
                <h1 class="text-base sm:text-lg font-extrabold text-slate-800">Presensi Saya</h1>
                <p class="text-xs text-slate-500">{{ student.name }} &bull; Kelas {{ student.class_name }}</p>
            </div>
        </template>

        <div class="space-y-6">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-500 font-semibold">Total Hari</p>
                    <p class="text-2xl font-black text-slate-800 mt-1">{{ summary.total }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-emerald-200 shadow-sm">
                    <p class="text-xs text-emerald-700 font-semibold">Hadir</p>
                    <p class="text-2xl font-black text-emerald-600 mt-1">{{ summary.hadir }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-amber-200 shadow-sm">
                    <p class="text-xs text-amber-700 font-semibold">Terlambat</p>
                    <p class="text-2xl font-black text-amber-600 mt-1">{{ summary.terlambat }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-sky-200 shadow-sm">
                    <p class="text-xs text-sky-700 font-semibold">Izin</p>
                    <p class="text-2xl font-black text-sky-600 mt-1">{{ summary.izin }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-indigo-200 shadow-sm">
                    <p class="text-xs text-indigo-700 font-semibold">Sakit</p>
                    <p class="text-2xl font-black text-indigo-600 mt-1">{{ summary.sakit }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-rose-200 shadow-sm">
                    <p class="text-xs text-rose-700 font-semibold">Alpa</p>
                    <p class="text-2xl font-black text-rose-600 mt-1">{{ summary.alpa }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
                    <h2 class="font-extrabold text-slate-800">Riwayat Kehadiran Siswa</h2>
                </div>

                <div v-if="attendances.length === 0" class="p-8 text-center text-sm text-slate-500">
                    Belum ada catatan presensi siswa.
                </div>

                <div v-else class="divide-y divide-slate-100">
                    <div
                        v-for="item in attendances"
                        :key="item.id"
                        class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 hover:bg-slate-50"
                    >
                        <div>
                            <p class="font-bold text-sm text-slate-800">{{ item.day_name }}, {{ item.date }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Masuk: {{ item.time_in }} &bull; Pulang: {{ item.time_out }}
                            </p>
                            <p v-if="item.leave_reason" class="text-xs text-slate-600 mt-1">
                                Keterangan: {{ item.leave_reason }}
                            </p>
                        </div>
                        <span
                            class="w-fit px-3 py-1 rounded-full text-xs font-extrabold"
                            :class="badgeClass(item.status)"
                        >
                            {{ item.status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>

