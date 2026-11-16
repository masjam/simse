<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    exam: { type: Object, required: true },
    attempts: { type: Array, default: () => [] },
});

const toast = useToast();
const selectedAttempt = ref(props.attempts[0] || null);

// Grading input state per essay
const gradeForm = ref({
    score: 0,
    comment: '',
});
const activeQuestionId = ref(null);
const savingGrade = ref(false);

const openGradeEssay = (essay) => {
    activeQuestionId.value = essay.question_id;
    gradeForm.value.score = essay.score || 0;
    gradeForm.value.comment = essay.comment || '';
};

const saveEssayGrade = async (attemptId, maxMarks) => {
    if (gradeForm.value.score > maxMarks) {
        toast.error(`Skor tidak boleh melebihi bobot soal (${maxMarks} poin).`);
        return;
    }

    savingGrade.value = true;
    try {
        const res = await window.axios.post(route('guru.cbt.grading.store', attemptId), {
            question_id: activeQuestionId.value,
            score: gradeForm.value.score,
            comment: gradeForm.value.comment,
        });

        toast.success(res.data.message || 'Nilai esai berhasil disimpan.');

        // Update local state
        if (selectedAttempt.value) {
            selectedAttempt.value.total_score = res.data.new_total_score;
            selectedAttempt.value.needs_manual_grading = res.data.needs_manual_grading;

            const essayObj = selectedAttempt.value.essays.find(e => e.question_id === activeQuestionId.value);
            if (essayObj) {
                essayObj.score = gradeForm.value.score;
                essayObj.comment = gradeForm.value.comment;
                essayObj.is_graded = true;
            }
        }
        activeQuestionId.value = null;
    } catch (e) {
        toast.error(e.response?.data?.message || 'Gagal menyimpan nilai.');
    } finally {
        savingGrade.value = false;
    }
};
</script>

<template>
    <Head :title="`Koreksi Esai CBT: ${exam.title}`" />

    <PortalShell variant="sidebar">
        <template #header>
            <PageHeader
                :title="`Antrean Koreksi Esai: ${exam.title}`"
                :description="`${exam.subject} &bull; Terdapat ${exam.essay_count} butir soal esai yang memerlukan penilaian guru.`"
            >
                <template #actions>
                    <Link :href="route('guru.cbt')">
                        <AppButton size="sm" variant="ghost">
                            &larr; Kembali ke Bank Soal
                        </AppButton>
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Left Column: Student Attempts List -->
            <div class="bg-surface rounded-card border border-line p-4 shadow-card space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-line">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-ink-muted">
                        Daftar Peserta ({{ attempts.length }})
                    </h3>
                </div>

                <div v-if="attempts.length === 0" class="text-xs text-ink-muted py-6 text-center">
                    Belum ada siswa yang menyelesaikan ujian ini.
                </div>
                <div v-else class="space-y-1.5 max-h-[600px] overflow-y-auto">
                    <div
                        v-for="att in attempts"
                        :key="att.attempt_id"
                        role="button"
                        tabindex="0"
                        :class="[
                            'w-full text-left p-3 rounded-control border transition-all space-y-1 cursor-pointer focus:outline-none focus:ring-2 focus:ring-focus',
                            selectedAttempt?.attempt_id === att.attempt_id
                                ? 'border-brand-600 bg-brand-50/50 ring-2 ring-brand-500/30'
                                : 'border-line bg-surface hover:bg-surface-raised'
                        ]"
                        @click="selectedAttempt = att; activeQuestionId = null"
                        @keydown.enter="selectedAttempt = att; activeQuestionId = null"
                    >
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-xs text-ink">{{ att.name }}</span>
                            <span class="font-mono text-xs font-bold text-brand-700">
                                {{ att.total_score !== null ? att.total_score : '-' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-ink-muted">
                            <span>{{ att.nis }} &bull; {{ att.class }}</span>
                            <span
                                v-if="att.needs_manual_grading"
                                class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-status-warning-bg text-status-warning-fg"
                            >
                                Belum Selesai ({{ att.pending_count }})
                            </span>
                            <span
                                v-else
                                class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-status-success-bg text-status-success-fg"
                            >
                                Lengkap
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Essay Answers & Grading Panel -->
            <div class="lg:col-span-2 bg-surface rounded-card border border-line p-5 shadow-card space-y-4">
                <div v-if="!selectedAttempt" class="text-xs text-ink-muted py-12 text-center">
                    Pilih salah satu peserta dari daftar sebelah kiri untuk memulai koreksi.
                </div>

                <div v-else class="space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-line flex-wrap gap-2">
                        <div>
                            <h3 class="text-sm font-bold text-ink">{{ selectedAttempt.name }}</h3>
                            <p class="text-xs text-ink-muted">{{ selectedAttempt.nis }} &bull; {{ selectedAttempt.class }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-ink-muted block">Total Nilai CBT:</span>
                            <span class="text-lg font-mono font-bold text-brand-600">{{ selectedAttempt.total_score }}</span>
                        </div>
                    </div>

                    <!-- Essay Items -->
                    <div class="space-y-4">
                        <div
                            v-for="(essay, idx) in selectedAttempt.essays"
                            :key="essay.question_id"
                            class="border border-line rounded-card p-4 space-y-3 bg-surface-raised/40"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <span class="px-2.5 py-0.5 rounded bg-brand-100 text-brand-800 font-bold font-mono text-xs">
                                    Soal Esai {{ idx + 1 }} &bull; Max {{ essay.max_marks }} Poin
                                </span>
                                <span
                                    :class="[
                                        'px-2 py-0.5 rounded text-[10px] font-bold font-mono',
                                        essay.is_graded ? 'bg-status-success-bg text-status-success-fg' : 'bg-status-warning-bg text-status-warning-fg'
                                    ]"
                                >
                                    {{ essay.is_graded ? `Dinilai: ${essay.score} Poin` : 'Belum Dinilai' }}
                                </span>
                            </div>

                            <!-- Question Content -->
                            <p class="text-xs text-ink font-semibold whitespace-pre-line leading-relaxed">
                                {{ essay.question_content }}
                            </p>

                            <!-- Student Answer -->
                            <div class="p-3 rounded-control bg-surface border border-line space-y-1">
                                <span class="text-[10px] uppercase font-bold text-ink-muted tracking-wider block">
                                    Jawaban Siswa:
                                </span>
                                <p class="text-xs text-ink leading-relaxed whitespace-pre-line font-medium">
                                    {{ essay.student_answer || '(Siswa tidak menjawab)' }}
                                </p>
                            </div>

                            <!-- Teacher Grading Box -->
                            <div v-if="activeQuestionId === essay.question_id" class="p-3 rounded-control bg-brand-50 border border-brand-200 space-y-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-brand-900 mb-1">
                                            Beri Nilai (Maks: {{ essay.max_marks }} Poin):
                                        </label>
                                        <AppInput
                                            v-model="gradeForm.score"
                                            type="number"
                                            min="0"
                                            :max="essay.max_marks"
                                            step="0.5"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-brand-900 mb-1">
                                            Catatan / Komentar Guru (Opsional):
                                        </label>
                                        <AppInput
                                            v-model="gradeForm.comment"
                                            placeholder="Feedback pengerjaan..."
                                        />
                                    </div>
                                </div>
                                <div class="flex items-center justify-end gap-2">
                                    <AppButton size="sm" variant="ghost" @click="activeQuestionId = null">
                                        Batal
                                    </AppButton>
                                    <AppButton
                                        size="sm"
                                        variant="primary"
                                        :loading="savingGrade"
                                        @click="saveEssayGrade(selectedAttempt.attempt_id, essay.max_marks)"
                                    >
                                        Simpan Nilai & Sinkronkan
                                    </AppButton>
                                </div>
                            </div>
                            <div v-else class="flex items-center justify-between pt-1">
                                <span v-if="essay.comment" class="text-xs text-ink-muted italic">
                                    Komentar: "{{ essay.comment }}"
                                </span>
                                <span v-else></span>

                                <AppButton size="sm" variant="secondary" @click="openGradeEssay(essay)">
                                    {{ essay.is_graded ? 'Koreksi Ulang' : 'Beri Nilai' }}
                                </AppButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalShell>
</template>
