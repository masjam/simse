<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import Tabs from '@/Components/ui/Tabs.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import ScoreChip from '@/Components/domain/ScoreChip.vue';
import KopSurat from '@/Components/domain/KopSurat.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';

const props = defineProps({
    student: { type: Object, required: true },
    raportSubjects: { type: Array, default: () => [] },
    grades: { type: Array, default: () => [] },
    achievements: { type: Array, default: () => [] },
});

const activeTab = ref('raport');

const tabs = [
    { id: 'raport', label: 'Rekap e-Raport' },
    { id: 'detail', label: `Rincian Nilai Harian (${props.grades.length})` },
];

const raportColumns = [
    { key: 'subject_name', label: 'Mata Pelajaran' },
    { key: 'tugas', label: 'Tugas', class: 'text-center font-mono' },
    { key: 'kuis', label: 'Kuis', class: 'text-center font-mono' },
    { key: 'uts', label: 'UTS', class: 'text-center font-mono' },
    { key: 'uas', label: 'UAS', class: 'text-center font-mono' },
    { key: 'nilai_akhir', label: 'Nilai Akhir', class: 'text-center font-mono font-bold text-brand-600' },
    { key: 'predikat', label: 'Predikat', class: 'text-center' },
    { key: 'notes', label: 'Catatan Capaian' },
];

const printRaport = () => {
    window.print();
};
</script>

<template>
    <Head title="Nilai & e-Raport Siswa" />

    <PortalShell variant="bottom-nav">
        <template #header>
            <PageHeader
                title="Nilai & e-Raport"
                :description="`${student.name} • Kelas ${student.class_name}`"
            />
        </template>

        <div class="space-y-4">
            <!-- Navigation & Print Button (Screen only) -->
            <div class="flex flex-wrap items-center justify-between gap-3 print:hidden">
                <div class="w-full sm:w-auto">
                    <Tabs v-model="activeTab" :tabs="tabs" variant="pills" />
                </div>

                <AppButton
                    size="sm"
                    variant="primary"
                    @click="printRaport"
                >
                    Cetak Lembar Nilai
                </AppButton>
            </div>

            <!-- Kop Surat Khusus Cetak -->
            <div class="hidden print:block mb-4">
                <KopSurat />
                <div class="mt-3 text-center border-b border-line pb-2">
                    <h3 class="text-sm font-bold uppercase text-ink">Laporan Hasil Belajar Siswa</h3>
                    <p class="text-xs text-ink-muted">
                        {{ student.name }} &bull; NISN: {{ student.nisn }} &bull; Kelas: {{ student.class_name }}
                    </p>
                </div>
            </div>

            <!-- 1. Rekap e-Raport -->
            <div v-if="activeTab === 'raport'" class="space-y-3">
                <DataTable
                    :columns="raportColumns"
                    :items="raportSubjects"
                    empty-title="Belum Ada Nilai Raport"
                    empty-message="Guru mata pelajaran belum mempublikasikan rekap nilai e-raport semester ini."
                >
                    <template #cell-subject_name="{ item }">
                        <p class="font-bold text-xs">{{ item.subject_name }}</p>
                        <p class="text-[11px] text-ink-muted">{{ item.teacher_name }}</p>
                    </template>
                    <template #cell-predikat="{ item }">
                        <ScoreChip :score="item.nilai_akhir" :show-score="false" />
                    </template>
                </DataTable>
            </div>

            <!-- 2. Rincian Nilai Harian -->
            <div v-else class="space-y-3">
                <div v-if="grades.length === 0">
                    <EmptyState
                        title="Belum Ada Nilai Harian"
                        description="Belum ada riwayat penilaian tugas harian yang diinput oleh bapak/ibu guru."
                    />
                </div>
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div
                        v-for="grade in grades"
                        :key="grade.id"
                        class="bg-surface rounded-card border border-line p-3.5 shadow-card flex items-start justify-between gap-3"
                    >
                        <div class="min-w-0">
                            <span class="px-2 py-0.5 rounded-control bg-surface-raised text-[10px] font-bold text-ink-muted uppercase">
                                {{ grade.type }} &bull; {{ grade.subject }}
                            </span>
                            <h5 class="text-xs font-bold text-ink mt-1 truncate">{{ grade.title }}</h5>
                            <p class="text-[11px] text-ink-muted mt-0.5">{{ grade.date }} &bull; {{ grade.teacher }}</p>
                            <p v-if="grade.notes" class="text-[11px] text-ink-muted italic mt-1">"{{ grade.notes }}"</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <ScoreChip :score="grade.score" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalShell>
</template>
