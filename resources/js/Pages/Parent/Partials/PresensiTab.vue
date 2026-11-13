<script setup>
import { computed } from 'vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import StatCard from '@/Components/ui/StatCard.vue';

const props = defineProps({
    presensi: {
        type: Object,
        required: true,
    },
});

const summary = computed(() => props.presensi?.summary || { total: 0, hadir: 0, terlambat: 0, izin: 0, sakit: 0, alpa: 0 });
const records = computed(() => props.presensi?.records || []);

const attendancePct = computed(() => {
    const total = summary.value.total || 0;
    if (total === 0) return 100;
    const present = (summary.value.hadir || 0) + (summary.value.terlambat || 0);
    return Math.round((present / total) * 100);
});
</script>

<template>
    <div class="space-y-4">
        <!-- Summary Cards Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <StatCard
                title="Total Hari"
                :value="summary.total"
                unit="Hari"
            />
            <StatCard
                title="Hadir"
                :value="summary.hadir"
                unit="Hari"
            />
            <StatCard
                title="Terlambat"
                :value="summary.terlambat"
                unit="Hari"
            />
            <StatCard
                title="Izin"
                :value="summary.izin"
                unit="Hari"
            />
            <StatCard
                title="Sakit"
                :value="summary.sakit"
                unit="Hari"
            />
            <StatCard
                title="Alpa"
                :value="summary.alpa"
                unit="Hari"
            />
        </div>

        <div class="bg-surface rounded-card border border-line p-3.5 shadow-card flex items-center justify-between">
            <span class="text-xs font-semibold text-ink-muted">Tingkat Kehadiran Kumulatif:</span>
            <span class="text-sm font-black text-brand-600 font-mono">{{ attendancePct }}%</span>
        </div>

        <!-- Attendance Records -->
        <div class="bg-surface rounded-card border border-line shadow-card overflow-hidden">
            <div class="p-4 border-b border-line bg-surface-raised flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-bold text-ink">Catatan Riwayat Presensi</h4>
                    <p class="text-xs text-ink-muted">Log harian kehadiran masuk dan kepulangan ananda.</p>
                </div>
            </div>

            <div v-if="records.length === 0" class="p-6">
                <EmptyState
                    title="Belum Ada Log Presensi"
                    description="Belum ada catatan presensi harian untuk ananda pada periode ini."
                />
            </div>
            <div v-else class="divide-y divide-line">
                <div
                    v-for="item in records"
                    :key="item.id"
                    class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 hover:bg-surface-raised/40 transition-colors"
                >
                    <div>
                        <p class="text-sm font-bold text-ink">{{ item.day_name }}, {{ item.date }}</p>
                        <p class="text-xs text-ink-muted mt-0.5">
                            Masuk: {{ item.time_in || '-' }} &bull; Pulang: {{ item.time_out || '-' }}
                        </p>
                        <p v-if="item.leave_reason" class="text-xs text-ink-muted mt-1 italic">
                            Keterangan: {{ item.leave_reason }}
                        </p>
                    </div>
                    <div class="shrink-0">
                        <StatusBadge :status="item.status" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

