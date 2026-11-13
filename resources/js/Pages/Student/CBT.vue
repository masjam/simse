<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';

defineProps({
    student: { type: Object, required: true },
    exams: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="Ujian Online (CBT) Siswa" />

    <PortalShell variant="bottom-nav">
        <template #header>
            <PageHeader
                title="Ujian Online (CBT)"
                :description="`${student.name} • Kelas ${student.class_name}`"
            />
        </template>

        <div class="space-y-4">
            <!-- Rules Alert Card -->
            <div class="bg-surface rounded-card border border-brand-500/30 p-4 shadow-card">
                <div class="flex items-start gap-3">
                    <span class="text-xl">⚠️</span>
                    <div class="text-xs text-ink leading-relaxed">
                        <strong class="font-bold text-ink">Tata Tertib &amp; Sistem Anti-Kecurangan CBT:</strong>
                        <p class="text-ink-muted mt-0.5">
                            Ujian berlangsung dalam mode fokus (tanpa menu navigasi), layar penuh, pencegahan klik kanan, serta pemantauan otomatis perpindahan tab browser. Jika ambang batas pelanggaran terlampaui, sistem akan mengumpulkan ujian secara otomatis.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Exams List / Empty State -->
            <div v-if="exams.length === 0">
                <EmptyState
                    title="Belum Ada Ujian Terjadwal"
                    description="Saat ini belum ada jadwal ujian CBT online yang dibuka untuk kelas Anda."
                />
            </div>
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                <div
                    v-for="exam in exams"
                    :key="exam.id"
                    class="bg-surface rounded-card border border-line shadow-card p-4 flex flex-col justify-between gap-3"
                >
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2.5 py-0.5 rounded-control bg-brand-50 text-brand-700 text-xs font-bold">
                                {{ exam.subject }}
                            </span>
                            <StatusBadge :status="exam.status" />
                        </div>

                        <h3 class="text-sm font-bold text-ink">{{ exam.title }}</h3>
                        <p v-if="exam.description" class="text-xs text-ink-muted leading-relaxed">
                            {{ exam.description }}
                        </p>

                        <div class="grid grid-cols-2 gap-2 pt-1 text-xs">
                            <div class="p-2 rounded-control bg-surface-raised border border-line">
                                <span class="text-ink-muted block text-[10px]">Durasi Ujian</span>
                                <span class="font-bold font-mono text-ink">{{ exam.duration_minutes }} Menit</span>
                            </div>
                            <div class="p-2 rounded-control bg-surface-raised border border-line">
                                <span class="text-ink-muted block text-[10px]">Jumlah Soal</span>
                                <span class="font-bold font-mono text-ink">{{ exam.questions_count }} Butir</span>
                            </div>
                            <div class="p-2 rounded-control bg-surface-raised border border-line col-span-2">
                                <span class="text-ink-muted block text-[10px]">Jadwal Akses</span>
                                <span class="font-bold font-mono text-ink text-[11px]">{{ exam.start_time }} - {{ exam.end_time }}</span>
                            </div>
                        </div>

                        <div
                            v-if="exam.attempt && exam.attempt.finished_at"
                            class="p-2.5 rounded-control bg-brand-50 border border-brand-200 text-xs space-y-0.5"
                        >
                            <p class="font-bold text-brand-800">
                                Nilai PG / Benar-Salah: <strong class="font-mono text-sm">{{ exam.attempt.score ?? 0 }}</strong>
                            </p>
                            <p class="text-[11px] text-brand-700">
                                Selesai pada: {{ exam.attempt.finished_at }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-line flex justify-end">
                        <Link v-if="exam.can_start" :href="route('siswa.cbt.start', exam.id)">
                            <AppButton size="sm" variant="primary">
                                Mulai Kerjakan Ujian
                            </AppButton>
                        </Link>
                        <span
                            v-else-if="exam.status === 'Selesai'"
                            class="text-xs font-bold text-status-success-fg"
                        >
                            Sudah Dikumpulkan
                        </span>
                        <span
                            v-else
                            class="text-xs font-semibold text-ink-muted"
                        >
                            Belum Dapat Diakses
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </PortalShell>
</template>
