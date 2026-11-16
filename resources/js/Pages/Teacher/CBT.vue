<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import CreateExamModal from './Partials/CreateExamModal.vue';
import QuestionEditorModal from './Partials/QuestionEditorModal.vue';
import ImportQuestionsModal from './Partials/ImportQuestionsModal.vue';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const props = defineProps({
    classes: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    students: { type: Array, default: () => [] },
    exams: { type: Array, default: () => [] },
});

const toast = useToast();
const { confirm } = useConfirm();

const activeTab = ref('all'); // 'all' | 'remedial'
const showCreateExam = ref(false);
const showQuestionEditor = ref(false);
const showImportModal = ref(false);
const activeExam = ref(null);
const questionToEdit = ref(null);
const expandedExamId = ref(null);

const filteredExams = computed(() => {
    if (activeTab.value === 'remedial') {
        return props.exams.filter((e) => e.isRemedial);
    }
    return props.exams;
});

const toggleStatus = (examId) => {
    router.patch(route('guru.cbt.exam.toggle', examId), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Status aktif ujian CBT berhasil diperbarui.');
        },
    });
};

const openAddQuestion = (exam) => {
    activeExam.value = exam;
    questionToEdit.value = null;
    showQuestionEditor.value = true;
};

const openEditQuestion = (exam, q) => {
    activeExam.value = exam;
    questionToEdit.value = q;
    showQuestionEditor.value = true;
};

const openImport = (exam) => {
    activeExam.value = exam;
    showImportModal.value = true;
};

const deleteQuestion = async (qId) => {
    const ok = await confirm({
        title: 'Hapus Butir Soal',
        message: 'Apakah Anda yakin ingin menghapus butir soal ini dari paket ujian?',
        confirmText: 'Hapus Soal',
        type: 'danger',
    });
    if (!ok) return;

    router.delete(route('guru.cbt.question.destroy', qId), {
        preserveScroll: true,
        onSuccess: () => toast.success('Butir soal berhasil dihapus.'),
    });
};
</script>

<template>
    <Head title="Bank Soal & CBT Lanjutan" />

    <PortalShell variant="sidebar">
        <template #header>
            <PageHeader
                title="Bank Soal & Ujian CBT Lanjutan"
                description="Computer Based Test dengan 6 Tipe Soal & Anti-Cheat Engine SD Muhammadiyah Al Mujahidin"
            />
        </template>

        <div class="space-y-4">
            <!-- Header Action Bar & Tabs -->
            <div class="bg-surface rounded-card border border-line p-4 shadow-card flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <AppButton
                        size="sm"
                        :variant="activeTab === 'all' ? 'primary' : 'secondary'"
                        @click="activeTab = 'all'"
                    >
                        Semua Paket Ujian ({{ exams.length }})
                    </AppButton>
                    <AppButton
                        size="sm"
                        :variant="activeTab === 'remedial' ? 'primary' : 'secondary'"
                        @click="activeTab = 'remedial'"
                    >
                        Ujian Remedial ({{ exams.filter(e => e.isRemedial).length }})
                    </AppButton>
                </div>

                <AppButton size="sm" variant="primary" @click="showCreateExam = true">
                    + Jadwalkan Paket Ujian
                </AppButton>
            </div>

            <!-- Exams List / Empty State -->
            <div v-if="filteredExams.length === 0">
                <EmptyState
                    title="Belum Ada Paket Ujian"
                    description="Belum ada ujian CBT pada kategori ini. Silakan buat paket ujian baru."
                    action-label="+ Jadwalkan Paket Ujian"
                    @action="showCreateExam = true"
                />
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div
                    v-for="exam in filteredExams"
                    :key="exam.id"
                    class="bg-surface rounded-card border border-line p-4 shadow-card hover:bg-surface-raised/30 transition-all flex flex-col justify-between gap-3.5"
                >
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-control bg-brand-50 text-brand-700 text-xs font-bold">
                                    {{ exam.subject }}
                                </span>
                                <span v-if="exam.isRemedial" class="px-2 py-0.5 rounded-control bg-status-warning-bg text-status-warning-fg text-[10px] font-bold">
                                    Remedial
                                </span>
                                <span class="px-2 py-0.5 rounded-control bg-surface-raised border border-line text-ink-muted text-[10px] font-mono">
                                    {{ exam.scoringRule === 'partial' ? 'Nilai Parsial' : 'Standar' }}
                                </span>
                            </div>
                            <StatusBadge :status="exam.status === 'active' ? 'Aktif' : (exam.status === 'completed' ? 'Selesai' : 'Terjadwal')" />
                        </div>

                        <div>
                            <h4 class="text-sm font-bold text-ink">{{ exam.title }}</h4>
                            <p v-if="exam.description" class="text-xs text-ink-muted leading-relaxed line-clamp-2 mt-0.5">
                                {{ exam.description }}
                            </p>
                        </div>

                        <div class="grid grid-cols-3 gap-2 pt-1 text-xs">
                            <div class="p-2 rounded-control bg-surface-raised border border-line text-center">
                                <span class="text-[10px] text-ink-muted block">Durasi</span>
                                <span class="font-bold font-mono text-ink">{{ exam.duration }} Mnt</span>
                            </div>
                            <div class="p-2 rounded-control bg-surface-raised border border-line text-center">
                                <span class="text-[10px] text-ink-muted block">Butir Soal</span>
                                <span class="font-bold font-mono text-ink">{{ exam.totalQuestions || 0 }} Butir</span>
                            </div>
                            <div class="p-2 rounded-control bg-surface-raised border border-line text-center">
                                <span class="text-[10px] text-ink-muted block">Rata-Rata</span>
                                <span class="font-bold font-mono text-brand-600">{{ exam.avgScore !== null ? exam.avgScore : '-' }}</span>
                            </div>
                        </div>

                        <div class="text-[11px] text-ink-muted flex items-center justify-between pt-1">
                            <span>Jadwal: {{ exam.date }} ({{ exam.time }})</span>
                            <span v-if="exam.pendingEssayCount > 0" class="px-2 py-0.5 rounded-full bg-status-warning-bg text-status-warning-fg font-bold text-[10px]">
                                {{ exam.pendingEssayCount }} Esai Belum Dinilai
                            </span>
                        </div>
                    </div>

                    <!-- Action Links & Management Buttons -->
                    <div class="pt-3 border-t border-line space-y-2">
                        <!-- Quick Links to Monitor, Grading, Report -->
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <Link :href="route('guru.cbt.monitor', exam.id)">
                                <AppButton size="sm" variant="secondary" class="!px-2 !py-0.5 text-xs">
                                    Monitor Realtime
                                </AppButton>
                            </Link>
                            <Link :href="route('guru.cbt.grading', exam.id)">
                                <AppButton size="sm" variant="secondary" class="!px-2 !py-0.5 text-xs">
                                    Koreksi Esai
                                </AppButton>
                            </Link>
                            <Link :href="route('guru.cbt.report', exam.id)">
                                <AppButton size="sm" variant="ghost" class="!px-2 !py-0.5 text-xs">
                                    Laporan Hasil
                                </AppButton>
                            </Link>
                        </div>

                        <!-- Question Bank Operations -->
                        <div class="flex items-center justify-between pt-1">
                            <div class="flex items-center gap-1.5">
                                <AppButton size="sm" variant="primary" class="!px-2 !py-0.5 text-xs" @click="openAddQuestion(exam)">
                                    + Butir Soal
                                </AppButton>
                                <AppButton size="sm" variant="secondary" class="!px-2 !py-0.5 text-xs" @click="openImport(exam)">
                                    Impor Excel
                                </AppButton>
                                <AppButton
                                    size="sm"
                                    variant="ghost"
                                    class="!px-1.5 !py-0.5 text-xs font-semibold text-brand-600"
                                    @click="expandedExamId = expandedExamId === exam.id ? null : exam.id"
                                >
                                    {{ expandedExamId === exam.id ? 'Tutup Soal' : `Lihat (${exam.questions.length})` }}
                                </AppButton>
                            </div>

                            <AppButton
                                size="sm"
                                :variant="exam.isActive ? 'danger' : 'secondary'"
                                class="!px-2.5 !py-0.5 text-xs"
                                @click="toggleStatus(exam.id)"
                            >
                                {{ exam.isActive ? 'Tutup Ujian' : 'Buka Ujian' }}
                            </AppButton>
                        </div>

                        <!-- Question List Accordion -->
                        <div v-if="expandedExamId === exam.id" class="pt-2 border-t border-line space-y-2 max-h-56 overflow-y-auto">
                            <div
                                v-for="(q, qIdx) in exam.questions"
                                :key="q.id"
                                class="p-2 rounded bg-surface-raised border border-line flex items-center justify-between text-xs gap-2"
                            >
                                <div class="flex items-center gap-2 truncate">
                                    <span class="w-5 text-center font-mono font-bold text-ink-muted">{{ qIdx + 1 }}.</span>
                                    <span class="px-1.5 py-0.2 rounded bg-brand-50 text-brand-700 text-[10px] font-bold shrink-0">
                                        {{ q.type }}
                                    </span>
                                    <span class="truncate text-ink">{{ q.content }}</span>
                                </div>
                                <div class="flex items-center gap-1 shrink-0">
                                    <AppButton
                                        size="sm"
                                        variant="ghost"
                                        class="!px-1.5 !py-0.5 text-[11px] font-bold text-brand-600"
                                        @click="openEditQuestion(exam, q)"
                                    >
                                        Edit
                                    </AppButton>
                                    <AppButton
                                        size="sm"
                                        variant="ghost"
                                        class="!px-1.5 !py-0.5 text-[11px] font-bold text-status-danger-fg"
                                        @click="deleteQuestion(q.id)"
                                    >
                                        Hapus
                                    </AppButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <CreateExamModal
            :show="showCreateExam"
            :subjects="subjects"
            :students="students"
            @close="showCreateExam = false"
        />

        <QuestionEditorModal
            :show="showQuestionEditor"
            :exam="activeExam"
            :question-to-edit="questionToEdit"
            @close="showQuestionEditor = false"
        />

        <ImportQuestionsModal
            :show="showImportModal"
            :exam="activeExam"
            @close="showImportModal = false"
        />
    </PortalShell>
</template>
