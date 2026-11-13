<script setup>
import { Head } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';

defineProps({
    student: { type: Object, required: true },
    summary: { type: Object, required: true },
    attendances: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="Presensi Saya" />

    <PortalShell variant="bottom-nav">
        <template #header>
            <PageHeader
                title="Presensi Saya"
                :description="`${student.name} • Kelas ${student.class_name}`"
            />
        </template>

        <div class="space-y-4">
            <!-- Stat Cards Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <StatCard title="Total Hari" :value="summary.total" unit="Hari" />
                <StatCard title="Hadir" :value="summary.hadir" unit="Hari" />
                <StatCard title="Terlambat" :value="summary.terlambat" unit="Hari" />
                <StatCard title="Izin" :value="summary.izin" unit="Hari" />
                <StatCard title="Sakit" :value="summary.sakit" unit="Hari" />
                <StatCard title="Alpa" :value="summary.alpa" unit="Hari" />
            </div>

            <!-- Attendance Records Table -->
            <div class="bg-surface rounded-card border border-line shadow-card overflow-hidden">
                <div class="p-4 border-b border-line bg-surface-raised flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold text-ink">Riwayat Kehadiran</h4>
                        <p class="text-xs text-ink-muted">Pencatatan presensi masuk dan jam kepulangan.</p>
                    </div>
                </div>

                <div v-if="attendances.length === 0" class="p-6">
                    <EmptyState
                        title="Belum Ada Data Presensi"
                        description="Belum ada riwayat kehadiran siswa yang tercatat pada semester ini."
                    />
                </div>
                <div v-else class="divide-y divide-line">
                    <div
                        v-for="item in attendances"
                        :key="item.id"
                        class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 hover:bg-surface-raised/40 transition-colors"
                    >
                        <div>
                            <p class="font-bold text-xs sm:text-sm text-ink">{{ item.day_name }}, {{ item.date }}</p>
                            <p class="text-xs text-ink-muted mt-0.5">
                                Masuk: {{ item.time_in || '-' }} &bull; Pulang: {{ item.time_out || '-' }}
                            </p>
                            <p v-if="item.leave_reason" class="text-xs text-ink-muted mt-1 italic">
                                Keterangan: {{ item.leave_reason }}
                            </p>
                        </div>
                        <div class="shrink-0 self-start sm:self-auto">
                            <StatusBadge :status="item.status" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalShell>
</template>
