<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    student: { type: Object, required: true },
    schedules: { type: Array, default: () => [] },
    todayIso: { type: Number, default: 1 },
});

const days = [
    { id: 1, label: 'Senin' },
    { id: 2, label: 'Selasa' },
    { id: 3, label: 'Rabu' },
    { id: 4, label: 'Kamis' },
    { id: 5, label: 'Jumat' },
    { id: 6, label: 'Sabtu' },
];

const groupedByDay = computed(() => {
    return days.map((day) => ({
        ...day,
        isToday: day.id === props.todayIso,
        items: props.schedules.filter((item) => item.day === day.id),
    }));
});
</script>

<template>
    <Head title="Jadwal Pelajaran Siswa" />

    <StudentLayout>
        <template #header>
            <div>
                <h1 class="text-base sm:text-lg font-extrabold text-slate-800">Jadwal Pelajaran</h1>
                <p class="text-xs text-slate-500">Kelas {{ student.class_name }} &bull; Wali Kelas: {{ student.wali_kelas }}</p>
            </div>
        </template>

        <div class="space-y-4">
            <div v-if="schedules.length === 0" class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-500 text-sm">
                Jadwal pelajaran untuk kelas {{ student.class_name }} belum tersedia.
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="day in groupedByDay"
                    :key="day.id"
                    class="bg-white rounded-2xl border shadow-sm overflow-hidden"
                    :class="day.isToday ? 'border-teal-400 ring-2 ring-teal-100' : 'border-slate-200'"
                >
                    <div
                        class="px-4 py-3 border-b flex items-center justify-between"
                        :class="day.isToday ? 'bg-teal-600 text-white' : 'bg-slate-50 text-slate-800 border-slate-200'"
                    >
                        <h2 class="font-extrabold text-sm">{{ day.label }}</h2>
                        <span v-if="day.isToday" class="px-2 py-0.5 rounded-full bg-white/20 text-[10px] font-bold uppercase">
                            Hari Ini
                        </span>
                    </div>

                    <div class="p-4 space-y-3">
                        <div v-if="day.items.length === 0" class="text-xs text-slate-400 text-center py-4">
                            Tidak ada jadwal pelajaran.
                        </div>
                        <div
                            v-for="item in day.items"
                            :key="item.id"
                            class="p-3 rounded-xl bg-slate-50 border border-slate-100"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <p class="font-bold text-sm text-slate-800">{{ item.subject }}</p>
                                <span class="px-2 py-0.5 rounded bg-sky-100 text-sky-800 text-[11px] font-bold shrink-0">
                                    {{ item.time_start }} - {{ item.time_end }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 mt-1">{{ item.teacher }}</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Ruang: {{ item.room }} &bull; {{ item.jpl }} JPL</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>

