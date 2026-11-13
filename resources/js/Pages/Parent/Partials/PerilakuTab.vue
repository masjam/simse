<script setup>
import { computed } from 'vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';

const props = defineProps({
    perilaku: {
        type: Object,
        required: true,
    },
});

const totalPoints = computed(() => props.perilaku?.total_points || 0);
const spStatus = computed(() => props.perilaku?.sp_status || 'Aman');
const violations = computed(() => props.perilaku?.violations || []);
const counselingSessions = computed(() => props.perilaku?.counseling_sessions || []);
</script>

<template>
    <div class="space-y-4">
        <!-- Status Pembinaan Banner -->
        <div class="bg-surface rounded-card border border-line p-4 shadow-card flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <span class="text-xs font-bold text-ink-muted uppercase tracking-wider">Status Pembinaan Siswa</span>
                <div class="flex items-center gap-2 mt-1">
                    <StatusBadge :status="spStatus" />
                    <span class="text-xs text-ink-muted">Akumulasi Poin: <strong class="text-ink font-mono">{{ totalPoints }}</strong></span>
                </div>
            </div>
            <p class="text-xs text-ink-muted max-w-md">
                Sekolah mengedepankan pembinaan karakter akhlak karimah melalui pendampingan wali kelas dan bimbingan konseling.
            </p>
        </div>

        <!-- Daftar Catatan Pelanggaran -->
        <div class="bg-surface rounded-card border border-line shadow-card overflow-hidden">
            <div class="p-4 border-b border-line bg-surface-raised">
                <h4 class="text-sm font-bold text-ink">Catatan Pelanggaran &amp; Kedisiplinan</h4>
                <p class="text-xs text-ink-muted">Catatan ketertiban yang dicatat oleh tim kesiswaan / guru.</p>
            </div>

            <div v-if="violations.length === 0" class="p-6">
                <EmptyState
                    title="Alhamdulillah, Tidak Ada Pelanggaran"
                    description="Ananda memiliki catatan perilaku dan kedisiplinan yang sangat baik."
                />
            </div>
            <div v-else class="divide-y divide-line">
                <div
                    v-for="item in violations"
                    :key="item.id"
                    class="p-4 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 hover:bg-surface-raised/40 transition-colors"
                >
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <h5 class="text-sm font-bold text-ink">{{ item.title }}</h5>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-control bg-status-danger-bg text-status-danger-fg">
                                +{{ item.points }} Poin
                            </span>
                        </div>
                        <p v-if="item.description" class="text-xs text-ink-muted leading-relaxed">
                            {{ item.description }}
                        </p>
                        <p class="text-[11px] text-ink-muted">
                            {{ item.date }} &bull; Pencatat: {{ item.teacher_name }} (Kategori: {{ item.category }})
                        </p>
                    </div>
                    <div class="shrink-0 self-start sm:self-auto">
                        <StatusBadge :status="item.status || 'Tercatat'" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Sesi Bimbingan Konseling (BK) -->
        <div class="bg-surface rounded-card border border-line shadow-card overflow-hidden">
            <div class="p-4 border-b border-line bg-surface-raised">
                <h4 class="text-sm font-bold text-ink">Sesi Bimbingan Konseling (BK)</h4>
                <p class="text-xs text-ink-muted">Riwayat pendampingan minat, bakat, serta konsultasi ananda dengan Guru BK.</p>
            </div>

            <div v-if="counselingSessions.length === 0" class="p-6">
                <EmptyState
                    title="Belum Ada Sesi Konseling"
                    description="Belum ada catatan pertemuan bimbingan konseling yang dijadwalkan."
                />
            </div>
            <div v-else class="divide-y divide-line">
                <div
                    v-for="session in counselingSessions"
                    :key="session.id"
                    class="p-4 space-y-1.5 hover:bg-surface-raised/40 transition-colors"
                >
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-bold px-2 py-0.5 rounded-control bg-brand-50 text-brand-700">
                            {{ session.type }}
                        </span>
                        <span class="text-xs text-ink-muted">{{ session.date }} &bull; {{ session.teacher_name }}</span>
                    </div>
                    <h5 class="text-xs font-bold text-ink">Topik / Bahasan:</h5>
                    <p class="text-xs text-ink-muted leading-relaxed">{{ session.problem || '-' }}</p>
                    <div v-if="session.solution" class="pt-1 text-xs">
                        <span class="font-bold text-ink">Rekomendasi / Solusi:</span>
                        <p class="text-ink-muted leading-relaxed">{{ session.solution }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

