<script setup>
import { ref } from 'vue';
import ScoreChip from '@/Components/domain/ScoreChip.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import Tabs from '@/Components/ui/Tabs.vue';
import DataTable from '@/Components/ui/DataTable.vue';

const props = defineProps({
    akademik: {
        type: Object,
        required: true,
    },
    prestasi: {
        type: Array,
        default: () => [],
    },
    activeChild: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['open-achievement-modal']);

const subTab = ref('raport');

const subTabs = [
    { id: 'raport', label: 'e-Raport Capaian' },
    { id: 'rincian', label: `Rincian Nilai (${props.akademik.grades?.length || 0})` },
    { id: 'jadwal', label: 'Jadwal Kelas' },
    { id: 'jurnal', label: 'Materi Guru Mingguan' },
    { id: 'prestasi', label: `Prestasi (${props.prestasi.length})` },
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
</script>

<template>
    <div class="space-y-4">
        <!-- Sub-tabs navigation -->
        <Tabs v-model="subTab" :tabs="subTabs" variant="pills" />

        <!-- 1. e-Raport Capaian -->
        <div v-if="subTab === 'raport'" class="space-y-3">
            <DataTable
                :columns="raportColumns"
                :items="akademik.raportSubjects || []"
                empty-title="Belum Ada Nilai Raport"
                empty-message="Guru mata pelajaran belum mempublikasikan rekap nilai raport untuk ananda."
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
        <div v-else-if="subTab === 'rincian'" class="space-y-3">
            <div v-if="!akademik.grades || akademik.grades.length === 0">
                <EmptyState
                    title="Belum Ada Nilai Tugas / Harian"
                    description="Belum ada riwayat nilai tugas harian yang diinput oleh guru pengajar."
                />
            </div>
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div
                    v-for="grade in akademik.grades"
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
                    <div class="text-right shrink-0">
                        <ScoreChip :score="grade.score" />
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Jadwal Pelajaran -->
        <div v-else-if="subTab === 'jadwal'" class="space-y-3">
            <div v-if="!akademik.schedules || akademik.schedules.length === 0">
                <EmptyState
                    title="Jadwal Belum Tersedia"
                    description="Jadwal pelajaran kelas ananda belum disusun atau belum aktif."
                />
            </div>
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <div
                    v-for="schedule in akademik.schedules"
                    :key="schedule.id"
                    class="bg-surface rounded-card border border-line p-3.5 shadow-card"
                >
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="font-bold text-brand-600 bg-brand-50 px-2 py-0.5 rounded-control">
                            {{ schedule.day_name }}
                        </span>
                        <span class="font-mono text-ink-muted">{{ schedule.time_start }} - {{ schedule.time_end }}</span>
                    </div>
                    <h5 class="text-sm font-bold text-ink mt-1">{{ schedule.subject }}</h5>
                    <p class="text-xs text-ink-muted mt-0.5">{{ schedule.teacher }}</p>
                    <p class="text-[11px] text-ink-muted mt-0.5">Ruang {{ schedule.room }} ({{ schedule.jpl }} JPL)</p>
                </div>
            </div>
        </div>

        <!-- 4. Jurnal Materi Mingguan -->
        <div v-else-if="subTab === 'jurnal'" class="space-y-3">
            <div v-if="!akademik.weeklyJournals || akademik.weeklyJournals.length === 0">
                <EmptyState
                    title="Belum Ada Catatan Jurnal"
                    description="Guru belum mengisi ringkasan materi jurnal mingguan di kelas ananda."
                />
            </div>
            <div v-else class="space-y-3">
                <div
                    v-for="journal in akademik.weeklyJournals"
                    :key="journal.id"
                    class="bg-surface rounded-card border border-line p-4 shadow-card space-y-1.5"
                >
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <span class="px-2.5 py-0.5 rounded-control bg-brand-50 text-brand-700 text-xs font-bold">
                            {{ journal.subject }}
                        </span>
                        <span class="text-xs text-ink-muted">{{ journal.date }}</span>
                    </div>
                    <h5 class="text-sm font-bold text-ink">{{ journal.topic }}</h5>
                    <p class="text-xs text-ink-muted leading-relaxed">{{ journal.summary }}</p>
                    <p class="text-[11px] text-ink-muted pt-1 border-t border-line">
                        Guru Pengajar: {{ journal.teacher }} &bull; Pertemuan ke-{{ journal.meeting_number }}
                    </p>
                </div>
            </div>
        </div>

        <!-- 5. Prestasi Ananda -->
        <div v-else-if="subTab === 'prestasi'" class="space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-bold text-ink">Catatan Prestasi & Lomba</h4>
                    <p class="text-xs text-ink-muted">Daftar kejuaraan yang telah diraih oleh ananda.</p>
                </div>
                <AppButton
                    size="sm"
                    variant="primary"
                    @click="emit('open-achievement-modal')"
                >
                    + Ajukan Prestasi
                </AppButton>
            </div>

            <div v-if="!prestasi || prestasi.length === 0">
                <EmptyState
                    title="Belum Ada Prestasi Terdata"
                    description="Belum ada data prestasi. Silakan ajukan sertifikat lomba atau prestasi ananda."
                    action-label="Ajukan Prestasi Baru"
                    @action="emit('open-achievement-modal')"
                />
            </div>
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div
                    v-for="item in prestasi"
                    :key="item.id"
                    class="bg-surface rounded-card border border-line p-4 shadow-card flex flex-col justify-between gap-3"
                >
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2 py-0.5 rounded-control bg-brand-50 text-brand-700 border border-brand-200 text-[10px] font-bold">
                                {{ item.category }} &bull; {{ item.level }}
                            </span>
                            <span
                                class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                :class="item.status === 'Disetujui' ? 'bg-status-success-bg text-status-success-fg' : (item.status === 'Ditolak' ? 'bg-status-danger-bg text-status-danger-fg' : 'bg-status-warning-bg text-status-warning-fg')"
                            >
                                {{ item.status }}
                            </span>
                        </div>
                        <h5 class="text-sm font-bold text-ink mt-1.5">{{ item.name }}</h5>
                        <p class="text-xs text-ink-muted mt-0.5">Penyelenggara: {{ item.organizer }} ({{ item.year }})</p>
                        <p v-if="item.description" class="text-xs text-ink-muted mt-1 leading-relaxed">
                            {{ item.description }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-line text-xs">
                        <span class="text-ink-muted">{{ item.achievement_date }}</span>
                        <a
                            v-if="item.has_certificate"
                            :href="route('ortu.achievements.certificate', item.id)"
                            target="_blank"
                            class="font-bold text-brand-600 hover:text-brand-700 hover:underline"
                        >
                            Unduh Sertifikat &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
