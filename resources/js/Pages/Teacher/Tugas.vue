<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';
import FormField from '@/Components/ui/FormField.vue';
import Modal from '@/Components/ui/Modal.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    classes: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    lessons: { type: Array, default: () => [] },
    assignments: { type: Array, default: () => [] },
});

const toast = useToast();
const activeFilter = ref('all');

const filteredAssignments = computed(() => {
    return props.assignments.filter(a => activeFilter.value === 'all' || a.status === activeFilter.value);
});

const lessonOptions = computed(() => props.lessons.map(l => ({ value: l.id, label: `${l.title} (${l.subject_name || l.subject})` })));

// Create Assignment Modal
const showCreateModal = ref(false);
const createForm = useForm({
    title: '',
    lesson_id: props.lessons[0]?.id || '',
    description: '',
    due_date: new Date(Date.now() + 3 * 24 * 60 * 60 * 1000).toISOString().slice(0, 16),
    max_score: 100,
});

const submitAssignment = () => {
    createForm.post(route('guru.tugas.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
            toast.success('Tugas siswa berhasil dibuat.');
        },
    });
};

// Submissions Review Modal
const showReviewModal = ref(false);
const activeAssignment = ref(null);
const gradingForm = useForm({
    score: '',
    feedback: '',
});
const activeSubmissionId = ref(null);

const openReview = (assignment) => {
    activeAssignment.value = assignment;
    showReviewModal.value = true;
};

const selectSubmission = (sub) => {
    activeSubmissionId.value = sub.id;
    gradingForm.score = sub.score !== null ? sub.score : '';
    gradingForm.feedback = sub.feedback || '';
};

const submitGrade = (submissionId) => {
    gradingForm.post(route('guru.tugas.grade', submissionId), {
        preserveScroll: true,
        onSuccess: () => {
            activeSubmissionId.value = null;
            gradingForm.reset();
            toast.success('Nilai tugas siswa berhasil disimpan.');
        },
    });
};
</script>

<template>
    <Head title="Tugas Siswa" />

    <PortalShell variant="sidebar">
        <template #header>
            <PageHeader
                title="Tugas & Penilaian Siswa"
                description="Kelola Penugasan Harian, Koreksi Pengumpulan, dan Berikan Umpan Balik Siswa"
            />
        </template>

        <div class="space-y-4">
            <!-- Stats Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <StatCard title="Total Penugasan" :value="assignments.length" unit="Tugas" />
                <StatCard title="Tugas Aktif" :value="assignments.filter(a => a.status === 'active').length" unit="Tugas" />
                <StatCard title="Menunggu Koreksi" :value="assignments.reduce((acc, t) => acc + ((t.submitted || 0) - (t.graded || 0)), 0)" unit="Siswa" />
                <StatCard title="Tugas Selesai" :value="assignments.filter(a => a.status === 'completed').length" unit="Tugas" />
            </div>

            <!-- Action & Header -->
            <div class="bg-surface rounded-card border border-line p-4 shadow-card flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-ink">Daftar Tugas Belajar Siswa</h3>
                    <p class="text-xs text-ink-muted">Pantau siswa yang telah mengumpulkan dan berikan skor langsung.</p>
                </div>
                <AppButton size="sm" variant="primary" @click="showCreateModal = true">
                    + Buat Tugas Baru
                </AppButton>
            </div>

            <!-- Assignments Grid / Empty State -->
            <div v-if="filteredAssignments.length === 0">
                <EmptyState
                    title="Belum Ada Penugasan"
                    description="Belum ada tugas belajar yang dibuat untuk siswa. Silakan buat tugas baru."
                    action-label="+ Buat Tugas Baru"
                    @action="showCreateModal = true"
                />
            </div>
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                <div
                    v-for="a in filteredAssignments"
                    :key="a.id"
                    class="bg-surface rounded-card border border-line p-4 shadow-card hover:bg-surface-raised/40 transition-all flex flex-col justify-between gap-3"
                >
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded-control bg-brand-50 text-brand-700 text-xs font-bold">
                                {{ a.subject || a.lesson?.subject?.name || 'Mata Pelajaran' }}
                            </span>
                            <span class="text-xs text-ink-muted">Batas: {{ a.due_date }}</span>
                        </div>

                        <h4 class="text-sm font-bold text-ink">{{ a.title }}</h4>
                        <p v-if="a.description" class="text-xs text-ink-muted leading-relaxed line-clamp-2">
                            {{ a.description }}
                        </p>

                        <div class="grid grid-cols-2 gap-2 pt-1 text-xs">
                            <div class="p-2 rounded-control bg-surface-raised border border-line text-center">
                                <span class="text-[10px] text-ink-muted block">Mengumpulkan</span>
                                <span class="font-bold font-mono text-sm text-brand-600">{{ a.submitted || 0 }} Siswa</span>
                            </div>
                            <div class="p-2 rounded-control bg-surface-raised border border-line text-center">
                                <span class="text-[10px] text-ink-muted block">Sudah Dikoreksi</span>
                                <span class="font-bold font-mono text-sm text-ink">{{ a.graded || 0 }} Siswa</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-line flex justify-end">
                        <AppButton size="sm" variant="secondary" @click="openReview(a)">
                            Koreksi Pengumpulan &rarr;
                        </AppButton>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Assignment Modal -->
        <Modal :show="showCreateModal" title="Buat Tugas Siswa Baru" max-width="md" @close="showCreateModal = false">
            <form @submit.prevent="submitAssignment" class="space-y-3">
                <FormField label="Pilih Modul / Materi Terkait" :error="createForm.errors.lesson_id" required>
                    <AppSelect
                        v-model="createForm.lesson_id"
                        :options="lessonOptions"
                    />
                </FormField>

                <FormField label="Judul Penugasan" :error="createForm.errors.title" required>
                    <AppInput
                        v-model="createForm.title"
                        placeholder="Contoh: Latihan Soal Adab Berdoa"
                        required
                    />
                </FormField>

                <FormField label="Petunjuk Tugas" :error="createForm.errors.description">
                    <AppTextarea
                        v-model="createForm.description"
                        rows="3"
                        placeholder="Petunjuk detail pengerjaan untuk siswa..."
                    />
                </FormField>

                <div class="grid grid-cols-2 gap-3">
                    <FormField label="Batas Waktu (Deadline)" :error="createForm.errors.due_date" required>
                        <AppInput
                            v-model="createForm.due_date"
                            type="datetime-local"
                            required
                        />
                    </FormField>
                    <FormField label="Skor Maksimal" :error="createForm.errors.max_score" required>
                        <AppInput
                            v-model="createForm.max_score"
                            type="number"
                            min="1"
                            max="100"
                            required
                        />
                    </FormField>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-line">
                    <AppButton type="button" variant="ghost" size="sm" @click="showCreateModal = false">
                        Batal
                    </AppButton>
                    <AppButton type="submit" variant="primary" size="sm" :loading="createForm.processing">
                        Buat Tugas
                    </AppButton>
                </div>
            </form>
        </Modal>

        <!-- Submissions Review Modal -->
        <Modal :show="showReviewModal" :title="`Koreksi Tugas: ${activeAssignment?.title || ''}`" max-width="lg" @close="showReviewModal = false">
            <div v-if="activeAssignment" class="space-y-4 text-xs text-ink">
                <div v-if="!activeAssignment.submissions || activeAssignment.submissions.length === 0" class="py-6 text-center text-ink-muted">
                    Belum ada siswa yang mengumpulkan tugas ini.
                </div>
                <div v-else class="divide-y divide-line max-h-96 overflow-y-auto pr-1">
                    <div
                        v-for="sub in activeAssignment.submissions"
                        :key="sub.id"
                        class="py-3 space-y-2"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-bold text-xs text-ink">{{ sub.student_name || 'Siswa' }}</span>
                            <span class="font-mono text-ink-muted">{{ sub.submitted_at }}</span>
                        </div>
                        <p v-if="sub.text_answer" class="p-2.5 rounded-control bg-surface-raised border border-line text-xs whitespace-pre-line leading-relaxed">
                            {{ sub.text_answer }}
                        </p>
                        <a
                            v-if="sub.has_file"
                            :href="route('siswa.submissions.download', sub.id)"
                            class="font-bold text-brand-600 hover:underline inline-block text-xs"
                        >
                            📎 Unduh Lampiran Tugas Siswa
                        </a>

                        <!-- Grading Form -->
                        <div v-if="activeSubmissionId === sub.id" class="p-3 bg-brand-50 border border-brand-200 rounded-control space-y-2">
                            <FormField label="Beri Nilai (0-100)" required>
                                <AppInput
                                    v-model="gradingForm.score"
                                    type="number"
                                    min="0"
                                    max="100"
                                    placeholder="Nilai angka"
                                />
                            </FormField>
                            <FormField label="Catatan / Umpan Balik Guru">
                                <AppInput
                                    v-model="gradingForm.feedback"
                                    placeholder="Catatan motivasi atau evaluasi..."
                                />
                            </FormField>
                            <div class="flex justify-end gap-2 pt-1">
                                <AppButton size="sm" variant="ghost" @click="activeSubmissionId = null">Batal</AppButton>
                                <AppButton size="sm" variant="primary" :loading="gradingForm.processing" @click="submitGrade(sub.id)">
                                    Simpan Nilai
                                </AppButton>
                            </div>
                        </div>
                        <div v-else class="flex items-center justify-between pt-1">
                            <span class="font-semibold text-ink-muted">
                                Nilai Saat Ini: <strong class="text-brand-600 font-mono">{{ sub.score !== null ? sub.score : 'Belum dinilai' }}</strong>
                            </span>
                            <AppButton size="sm" variant="secondary" @click="selectSubmission(sub)">
                                {{ sub.score !== null ? 'Ubah Nilai' : 'Beri Nilai' }}
                            </AppButton>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2 border-t border-line">
                    <AppButton size="sm" variant="ghost" @click="showReviewModal = false">
                        Tutup
                    </AppButton>
                </div>
            </div>
        </Modal>
    </PortalShell>
</template>
