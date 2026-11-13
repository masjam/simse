<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';

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

    <PortalShell variant="bottom-nav">
        <template #header>
            <PageHeader
                title="Jadwal Pelajaran"
                :description="`Kelas ${student.class_name} • Wali Kelas: ${student.wali_kelas}`"
            />
        </template>

        <div class="space-y-4">
            <div v-if="schedules.length === 0">
                <EmptyState
                    title="Jadwal Belum Tersedia"
                    :description="`Jadwal pelajaran untuk kelas ${student.class_name} belum dipublikasikan.`"
                />
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                <div
                    v-for="day in groupedByDay"
                    :key="day.id"
                    class="bg-surface rounded-card border shadow-card overflow-hidden flex flex-col justify-between"
                    :class="day.isToday ? 'border-brand-500 ring-2 ring-brand-500/20' : 'border-line'"
                >
                    <div
                        class="px-4 py-3 border-b flex items-center justify-between"
                        :class="day.isToday ? 'bg-brand-600 text-brand-fg border-brand-700' : 'bg-surface-raised text-ink border-line'"
                    >
                        <h3 class="font-bold text-sm">{{ day.label }}</h3>
                        <span v-if="day.isToday" class="px-2 py-0.5 rounded-full bg-white/20 text-[10px] font-bold uppercase tracking-wider">
                            Hari Ini
                        </span>
                    </div>

                    <div class="p-3.5 space-y-2.5 flex-1">
                        <div v-if="day.items.length === 0" class="text-xs text-ink-muted text-center py-6">
                            Tidak ada kegiatan pelajaran.
                        </div>
                        <div
                            v-for="item in day.items"
                            :key="item.id"
                            class="p-3 rounded-control bg-surface-raised border border-line space-y-1"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <p class="font-bold text-xs sm:text-sm text-ink">{{ item.subject }}</p>
                                <span class="px-2 py-0.5 rounded-control bg-brand-50 text-brand-700 text-[11px] font-mono font-bold shrink-0">
                                    {{ item.time_start }} - {{ item.time_end }}
                                </span>
                            </div>
                            <p class="text-xs text-ink-muted">{{ item.teacher }}</p>
                            <p class="text-[11px] text-ink-muted">Ruang: {{ item.room }} &bull; {{ item.jpl }} JPL</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalShell>
</template>
