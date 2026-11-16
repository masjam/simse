<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import DataTable from '@/Components/ui/DataTable.vue';

const props = defineProps({
    exam: { type: Object, required: true },
    stats: { type: Object, required: true },
    students: { type: Array, default: () => [] },
    itemAnalysis: { type: Array, default: () => [] },
});

const itemColumns = [
    { key: 'number', label: 'No' },
    { key: 'type', label: 'Tipe Soal' },
    { key: 'content', label: 'Isi Pertanyaan' },
    { key: 'correct_ratio', label: 'Menjawab Benar' },
    { key: 'difficulty_pct', label: 'Tingkat Kesulitan' },
    { key: 'category', label: 'Kategori' },
];

const studentColumns = [
    { key: 'index', label: 'No' },
    { key: 'nis', label: 'NIS' },
    { key: 'name', label: 'Nama Siswa' },
    { key: 'class', label: 'Kelas' },
    { key: 'duration', label: 'Waktu Pengerjaan' },
    { key: 'violations', label: 'Pelanggaran' },
    { key: 'score', label: 'Nilai Akhir' },
];
</script>

<template>
    <Head :title="`Laporan & Analisis CBT: ${exam.title}`" />

    <PortalShell variant="sidebar">
        <template #header>
            <PageHeader
                :title="`Laporan Hasil: ${exam.title}`"
                :description="`${exam.subject} &bull; Guru: ${exam.teacher} &bull; Waktu: ${exam.start_time}`"
            >
                <template #actions>
                    <div class="flex items-center gap-2">
                        <a :href="route('guru.cbt.report.excel', exam.id)" target="_blank">
                            <AppButton size="sm" variant="secondary">
                                Ekspor Excel
                            </AppButton>
                        </a>
                        <a :href="route('guru.cbt.report.pdf', exam.id)" target="_blank">
                            <AppButton size="sm" variant="primary">
                                Unduh PDF Resmi
                            </AppButton>
                        </a>
                        <Link :href="route('guru.cbt')">
                            <AppButton size="sm" variant="ghost">
                                &larr; Kembali
                            </AppButton>
                        </Link>
                    </div>
                </template>
            </PageHeader>
        </template>

        <div class="space-y-5">
            <!-- 1. KPI Statistics Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-4 rounded-card bg-surface border border-line shadow-card text-center">
                    <span class="text-xs text-ink-muted block font-semibold">Total Peserta Selesai</span>
                    <span class="text-2xl font-bold font-numeric text-ink">{{ stats.total_participants }}</span>
                </div>
                <div class="p-4 rounded-card bg-surface border border-line shadow-card text-center">
                    <span class="text-xs text-ink-muted block font-semibold">Nilai Rata-Rata</span>
                    <span class="text-2xl font-bold font-numeric text-brand-600">{{ stats.avg_score }}</span>
                </div>
                <div class="p-4 rounded-card bg-surface border border-line shadow-card text-center">
                    <span class="text-xs text-ink-muted block font-semibold">Nilai Tertinggi</span>
                    <span class="text-2xl font-bold font-numeric text-status-success-fg">{{ stats.max_score }}</span>
                </div>
                <div class="p-4 rounded-card bg-surface border border-line shadow-card text-center">
                    <span class="text-xs text-ink-muted block font-semibold">Nilai Terendah</span>
                    <span class="text-2xl font-bold font-numeric text-status-danger-fg">{{ stats.min_score }}</span>
                </div>
            </div>

            <!-- 2. Grade Distribution Bar -->
            <div class="p-4 rounded-card bg-surface border border-line shadow-card space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-ink-muted">
                    Sebaran Distribusi Predikat Nilai
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs">
                    <div class="p-2.5 rounded-control bg-status-success-bg border border-status-success-line">
                        <span class="font-bold text-status-success-fg block text-sm">Predikat A (&ge; 85)</span>
                        <span class="font-numeric font-bold text-lg text-status-success-fg">{{ stats.distribution?.a || 0 }} Siswa</span>
                    </div>
                    <div class="p-2.5 rounded-control bg-status-info-bg border border-status-info-line">
                        <span class="font-bold text-status-info-fg block text-sm">Predikat B (70 - 84)</span>
                        <span class="font-numeric font-bold text-lg text-status-info-fg">{{ stats.distribution?.b || 0 }} Siswa</span>
                    </div>
                    <div class="p-2.5 rounded-control bg-status-warning-bg border border-status-warning-line">
                        <span class="font-bold text-status-warning-fg block text-sm">Predikat C (55 - 69)</span>
                        <span class="font-numeric font-bold text-lg text-status-warning-fg">{{ stats.distribution?.c || 0 }} Siswa</span>
                    </div>
                    <div class="p-2.5 rounded-control bg-status-danger-bg border border-status-danger-line">
                        <span class="font-bold text-status-danger-fg block text-sm">Predikat D (&lt; 55)</span>
                        <span class="font-numeric font-bold text-lg text-status-danger-fg">{{ stats.distribution?.d || 0 }} Siswa</span>
                    </div>
                </div>
            </div>

            <!-- 3. Item Analysis Table (Analisis Butir Soal & Daya Pembeda) -->
            <div class="bg-surface rounded-card border border-line shadow-card p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-ink">Analisis Butir Soal & Tingkat Kesulitan</h3>
                        <p class="text-xs text-ink-muted">Tingkat kesukaran soal dan persentase pilihan siswa.</p>
                    </div>
                </div>

                <DataTable :columns="itemColumns" :items="itemAnalysis">
                    <template #cell(number)="{ item }">
                        <span class="font-numeric font-bold">{{ item.number }}</span>
                    </template>
                    <template #cell(type)="{ item }">
                        <span class="px-2 py-0.5 rounded bg-brand-50 text-brand-700 font-bold text-[10px]">
                            {{ item.type }}
                        </span>
                    </template>
                    <template #cell(content)="{ item }">
                        <span class="line-clamp-2 max-w-md">{{ item.content }}</span>
                    </template>
                    <template #cell(correct_ratio)="{ item }">
                        <span class="font-numeric">{{ item.correct_count }} / {{ item.total_participants }}</span>
                    </template>
                    <template #cell(difficulty_pct)="{ item }">
                        <span class="font-numeric font-bold">{{ item.difficulty_pct }}</span>
                    </template>
                    <template #cell(category)="{ item }">
                        <span
                            :class="[
                                'px-2 py-0.5 rounded-full text-[10px] font-bold',
                                item.category === 'Mudah' ? 'bg-status-success-bg text-status-success-fg' :
                                (item.category === 'Sedang' ? 'bg-status-info-bg text-status-info-fg' : 'bg-status-danger-bg text-status-danger-fg')
                            ]"
                        >
                            {{ item.category }}
                        </span>
                    </template>
                </DataTable>
            </div>

            <!-- 4. Students Score Recap Table -->
            <div class="bg-surface rounded-card border border-line shadow-card p-4 space-y-3">
                <h3 class="text-sm font-bold text-ink">Rekapitulasi Nilai Peserta Ujian</h3>

                <DataTable :columns="studentColumns" :items="students">
                    <template #cell(index)="{ item }">
                        <span class="font-numeric">{{ students.indexOf(item) + 1 }}</span>
                    </template>
                    <template #cell(nis)="{ item }">
                        <span class="font-numeric">{{ item.nis }}</span>
                    </template>
                    <template #cell(name)="{ item }">
                        <span class="font-bold text-ink">{{ item.name }}</span>
                    </template>
                    <template #cell(class)="{ item }">
                        <span>{{ item.class }}</span>
                    </template>
                    <template #cell(duration)="{ item }">
                        <span class="text-[11px] text-ink-muted">
                            {{ item.started_at }} - {{ item.finished_at }}
                        </span>
                    </template>
                    <template #cell(violations)="{ item }">
                        <span v-if="item.violations_count > 0" class="text-status-danger-fg font-bold font-numeric">
                            {{ item.violations_count }}x
                        </span>
                        <span v-else class="text-ink-muted font-numeric">0</span>
                    </template>
                    <template #cell(score)="{ item }">
                        <span class="font-numeric font-black text-sm text-brand-600">
                            {{ item.score !== null ? item.score : '-' }}
                        </span>
                    </template>
                </DataTable>
            </div>
        </div>
    </PortalShell>
</template>
