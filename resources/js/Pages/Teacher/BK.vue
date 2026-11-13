<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import Tabs from '@/Components/ui/Tabs.vue';
import Modal from '@/Components/ui/Modal.vue';
import KopSurat from '@/Components/domain/KopSurat.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import ReportViolationModal from './Partials/ReportViolationModal.vue';
import CounselingModal from './Partials/CounselingModal.vue';

const props = defineProps({
    classes: { type: Array, default: () => [] },
    violations: { type: Array, default: () => [] },
    counselings: { type: Array, default: () => [] },
    violationPoints: { type: Array, default: () => [] },
    students: { type: Array, default: () => [] },
});

const activeTab = ref('pelanggaran');
const showReportModal = ref(false);
const showCounselingModal = ref(false);
const showBapModal = ref(false);
const activeViolation = ref(null);

const tabs = [
    { id: 'pelanggaran', label: `Catatan Pelanggaran (${props.violations.length})` },
    { id: 'konseling', label: `Sesi Bimbingan Konseling (${props.counselings.length})` },
];

const printBap = (violation) => {
    activeViolation.value = violation;
    showBapModal.value = true;
};

const printDoc = () => {
    window.print();
};
</script>

<template>
    <Head title="Bimbingan & Konseling (BK)" />

    <PortalShell variant="sidebar">
        <template #header>
            <PageHeader
                title="Bimbingan & Konseling (BK)"
                description="Pencatatan Kedisiplinan & Pendampingan Pembinaan Karakter Siswa SD Muhammadiyah Al Mujahidin"
            />
        </template>

        <div class="space-y-4">
            <!-- Tabs & Action Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <Tabs v-model="activeTab" :tabs="tabs" variant="pills" />

                <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
                    <AppButton
                        v-if="activeTab === 'pelanggaran'"
                        size="sm"
                        variant="primary"
                        @click="showReportModal = true"
                    >
                        + Catat Pelanggaran
                    </AppButton>
                    <AppButton
                        v-else
                        size="sm"
                        variant="primary"
                        @click="showCounselingModal = true"
                    >
                        + Catat Sesi Konseling
                    </AppButton>
                </div>
            </div>

            <!-- Tab 1: Catatan Pelanggaran -->
            <div v-if="activeTab === 'pelanggaran'" class="space-y-3">
                <div v-if="violations.length === 0">
                    <EmptyState
                        title="Tidak Ada Catatan Pelanggaran"
                        description="Alhamdulillah, belum ada catatan pelanggaran tata tertib siswa."
                        action-label="+ Catat Pelanggaran"
                        @action="showReportModal = true"
                    />
                </div>
                <div v-else class="space-y-3">
                    <div
                        v-for="v in violations"
                        :key="v.id"
                        class="bg-surface rounded-card border border-line p-4 shadow-card hover:bg-surface-raised/40 transition-all flex flex-col justify-between gap-3"
                    >
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-bold text-ink">{{ v.student_name }}</h4>
                                    <span class="text-xs text-ink-muted">Kelas {{ v.class_name }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded-control text-xs font-bold bg-status-danger-bg text-status-danger-fg border border-status-danger-line">
                                    +{{ v.points }} Poin
                                </span>
                            </div>

                            <p class="text-xs font-bold text-ink">{{ v.violation_name }}</p>
                            <p v-if="v.description" class="text-xs text-ink-muted leading-relaxed">
                                {{ v.description }}
                            </p>
                            <p class="text-[11px] text-ink-muted">
                                Tanggal: {{ v.date }} &bull; Pelapor: {{ v.reporter_name }}
                            </p>
                        </div>

                        <div class="pt-2 border-t border-line flex items-center justify-between text-xs">
                            <StatusBadge :status="v.status || 'Tercatat'" />
                            <AppButton size="sm" variant="secondary" @click="printBap(v)">
                                Cetak BAP &rarr;
                            </AppButton>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Sesi Bimbingan Konseling -->
            <div v-else class="space-y-3">
                <div v-if="counselings.length === 0">
                    <EmptyState
                        title="Belum Ada Sesi Konseling"
                        description="Belum ada riwayat bimbingan konseling yang dicatat. Silakan tambah sesi baru."
                        action-label="+ Catat Sesi Konseling"
                        @action="showCounselingModal = true"
                    />
                </div>
                <div v-else class="space-y-3">
                    <div
                        v-for="c in counselings"
                        :key="c.id"
                        class="bg-surface rounded-card border border-line p-4 shadow-card hover:bg-surface-raised/40 transition-all space-y-2"
                    >
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-control bg-brand-50 text-brand-700 text-xs font-bold">
                                    {{ c.type }}
                                </span>
                                <h4 class="text-xs sm:text-sm font-bold text-ink">{{ c.student_name }} ({{ c.class_name }})</h4>
                            </div>
                            <span class="text-xs font-mono text-ink-muted">{{ c.date }}</span>
                        </div>

                        <div class="text-xs space-y-1">
                            <p><strong class="text-ink">Persoalan:</strong> <span class="text-ink-muted">{{ c.problem }}</span></p>
                            <p v-if="c.solution"><strong class="text-ink">Rekomendasi / Solusi:</strong> <span class="text-ink-muted">{{ c.solution }}</span></p>
                        </div>

                        <div class="pt-2 border-t border-line flex items-center justify-between text-xs">
                            <span class="text-[11px] text-ink-muted">Konselor: {{ c.teacher_name || 'Guru BK' }}</span>
                            <StatusBadge :status="c.status" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <ReportViolationModal
            :show="showReportModal"
            :students="students"
            :violation-points="violationPoints"
            @close="showReportModal = false"
        />

        <CounselingModal
            :show="showCounselingModal"
            :students="students"
            @close="showCounselingModal = false"
        />

        <!-- Printable BAP Modal -->
        <Modal :show="showBapModal" title="Berita Acara Pembinaan Siswa (BAP)" max-width="lg" @close="showBapModal = false">
            <div v-if="activeViolation" class="space-y-4 text-xs text-ink">
                <KopSurat />

                <div class="text-center border-b border-line pb-2">
                    <h3 class="text-sm font-bold uppercase text-ink">BERITA ACARA PEMBINAAN KEDISIPLINAN</h3>
                    <p class="text-xs text-ink-muted">Nomor Surat: BAP/{{ activeViolation.id?.slice(0, 8) }}/SDMAM</p>
                </div>

                <div class="p-3 bg-surface-raised rounded-control border border-line space-y-1">
                    <p><span class="text-ink-muted">Nama Siswa:</span> <strong class="text-ink">{{ activeViolation.student_name }}</strong></p>
                    <p><span class="text-ink-muted">Kelas:</span> <strong class="text-ink">{{ activeViolation.class_name }}</strong></p>
                    <p><span class="text-ink-muted">Pelanggaran:</span> <strong class="text-ink">{{ activeViolation.violation_name }} (+{{ activeViolation.points }} Poin)</strong></p>
                    <p><span class="text-ink-muted">Kronologi:</span> <span class="text-ink">{{ activeViolation.description || '-' }}</span></p>
                </div>

                <div class="grid grid-cols-2 gap-4 text-center pt-4">
                    <div>
                        <p class="text-[11px] text-ink-muted">Siswa yang Bersangkutan,</p>
                        <div class="h-14"></div>
                        <p class="font-bold text-ink underline">{{ activeViolation.student_name }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] text-ink-muted">Guru Pembina / Wali Kelas,</p>
                        <div class="h-14"></div>
                        <p class="font-bold text-ink underline">{{ activeViolation.reporter_name }}</p>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-line print:hidden">
                    <AppButton size="sm" variant="ghost" @click="showBapModal = false">Tutup</AppButton>
                    <AppButton size="sm" variant="primary" @click="printDoc">Cetak BAP Resmi</AppButton>
                </div>
            </div>
        </Modal>
    </PortalShell>
</template>
