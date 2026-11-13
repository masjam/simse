<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import Tabs from '@/Components/ui/Tabs.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';

const props = defineProps({
    schedules: { type: Array, default: () => [] },
});

const todayDay = new Date().getDay() === 0 ? 1 : new Date().getDay();
const selectedDay = ref(String(todayDay));

const dayTabs = [
    { id: '1', label: 'Senin' },
    { id: '2', label: 'Selasa' },
    { id: '3', label: 'Rabu' },
    { id: '4', label: 'Kamis' },
    { id: '5', label: 'Jumat' },
    { id: '6', label: 'Sabtu' },
];

const allSchedules = computed(() => props.schedules?.length ? props.schedules : []);

const filteredSchedules = computed(() => {
    const dayNum = parseInt(selectedDay.value);
    return allSchedules.value.filter(s => (s.dayNumber ? s.dayNumber === dayNum : s.day === dayNum));
});

const totalJam = computed(() => filteredSchedules.value.reduce((acc, s) => acc + (s.jpl || 2), 0));
const totalStudents = computed(() => filteredSchedules.value.reduce((acc, s) => acc + (s.students || 0), 0));
</script>

<template>
    <Head title="Jadwal Mengajar Pendidik" />

    <PortalShell variant="sidebar">
        <template #header>
            <PageHeader
                title="Jadwal Mengajar Pendidik"
                description="Jadwal Kegiatan Belajar Mengajar Mingguan SD Muhammadiyah Al Mujahidin"
            />
        </template>

        <div class="space-y-4">
            <!-- Day Tabs -->
            <Tabs v-model="selectedDay" :tabs="dayTabs" variant="pills" />

            <!-- Stats Bar -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <StatCard title="Sesi Mengajar" :value="filteredSchedules.length" unit="Sesi" />
                <StatCard title="Total Durasi" :value="totalJam" unit="Jam Pelajaran" />
                <StatCard title="Total Siswa Diampu" :value="totalStudents" unit="Siswa" />
            </div>

            <!-- Schedules Cards -->
            <div v-if="filteredSchedules.length === 0">
                <EmptyState
                    title="Tidak Ada Jadwal Mengajar"
                    description="Tidak ada kegiatan belajar mengajar yang dijadwalkan pada hari ini."
                />
            </div>
            <div v-else class="space-y-3">
                <div
                    v-for="s in filteredSchedules"
                    :key="s.id"
                    class="bg-surface rounded-card border border-line p-4 shadow-card hover:bg-surface-raised/40 transition-all flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                >
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded-control bg-brand-50 text-brand-700 text-xs font-bold font-mono">
                                {{ s.time || `${s.time_start} - ${s.time_end}` }}
                            </span>
                            <span class="text-xs font-bold text-ink">
                                Kelas {{ s.class || s.student_class?.name }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-ink">{{ s.subject || s.subject?.name }}</h4>
                        <p class="text-xs text-ink-muted">
                            Ruang: {{ s.room || 'Ruang Teori' }} &bull; {{ s.jpl || 2 }} JPL &bull; {{ s.students || 30 }} Siswa
                        </p>
                    </div>

                    <div class="shrink-0 self-start sm:self-auto">
                        <span class="px-2.5 py-1 rounded-control bg-surface-raised text-ink-muted border border-line text-xs font-bold">
                            Tatap Muka
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </PortalShell>
</template>
