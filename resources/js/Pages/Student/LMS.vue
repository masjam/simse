<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import AssignmentSubmitModal from './Partials/AssignmentSubmitModal.vue';

const props = defineProps({
    student: { type: Object, required: true },
    subjects: { type: Array, default: () => [] },
    selectedSubjectId: { type: String, default: '' },
    lessons: { type: Array, default: () => [] },
    gamification: {
        type: Object,
        default: () => ({
            total_xp: 0,
            total_submissions: 0,
            level: { level: 1, title: 'Penuntut Ilmu Pemula', icon: '🌱' },
            badges: [],
        }),
    },
});

const subjectFilter = ref(props.selectedSubjectId || '');
const activeAssignment = ref(null);
const showSubmitModal = ref(false);

const subjectOptions = computed(() => [
    { value: '', label: 'Semua Mata Pelajaran' },
    ...props.subjects.map((s) => ({ value: s.id, label: s.name })),
]);

const filterBySubject = (val) => {
    subjectFilter.value = val;
    router.get(
        route('siswa.lms'),
        { subject_id: subjectFilter.value },
        { preserveState: true, preserveScroll: true }
    );
};

const openSubmitModal = (assignment) => {
    activeAssignment.value = assignment;
    showSubmitModal.value = true;
};
</script>

<template>
    <Head title="Materi & Tugas (LMS)" />

    <PortalShell variant="bottom-nav">
        <template #header>
            <PageHeader
                title="Materi & Tugas (LMS)"
                :description="`Kelas ${student.class_name} • Ruang Belajar Mandiri`"
            />
        </template>

        <div class="space-y-4">
            <!-- 1. Gamification Banner -->
            <div class="rounded-card border border-brand-500/30 p-4 sm:p-5 shadow-card bg-surface relative overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-control bg-brand-50 border border-brand-200 flex items-center justify-center text-2xl shadow-inner shrink-0">
                            {{ gamification.level?.icon || '🌱' }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-full bg-brand-600 text-brand-fg font-extrabold text-[10px] tracking-wide uppercase">
                                    Level {{ gamification.level?.level || 1 }}
                                </span>
                                <h3 class="text-sm sm:text-base font-black text-ink">
                                    {{ gamification.level?.title || 'Penuntut Ilmu' }}
                                </h3>
                            </div>
                            <p class="text-xs text-ink-muted mt-0.5">
                                Total Perolehan: <strong class="text-brand-600 font-bold font-mono">{{ gamification.total_xp }} XP</strong> &bull; {{ gamification.total_submissions }} Tugas Diserahkan
                            </p>
                        </div>
                    </div>

                    <!-- Badges -->
                    <div class="flex flex-wrap items-center gap-1.5">
                        <div
                            v-for="badge in gamification.badges"
                            :key="badge.name"
                            class="px-2.5 py-1 rounded-control border text-xs flex items-center gap-1.5 transition-all"
                            :class="badge.unlocked ? 'bg-brand-50 border-brand-200 text-brand-800 font-bold' : 'bg-surface-raised border-line text-ink-muted opacity-50 grayscale'"
                            :title="badge.desc"
                        >
                            <span>{{ badge.icon }}</span>
                            <span class="text-[11px]">{{ badge.name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Subject Filter -->
            <div class="bg-surface rounded-card border border-line p-3.5 shadow-card flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <label for="lms-subject-filter" class="text-xs font-bold text-ink shrink-0">
                    Filter Berdasarkan Mata Pelajaran:
                </label>
                <div class="w-full sm:w-64">
                    <AppSelect
                        id="lms-subject-filter"
                        :model-value="subjectFilter"
                        :options="subjectOptions"
                        @update:model-value="filterBySubject"
                    />
                </div>
            </div>

            <!-- 3. Lessons List -->
            <div v-if="lessons.length === 0">
                <EmptyState
                    title="Belum Ada Materi Pembelajaran"
                    description="Guru belum mengunggah materi atau tugas pada mata pelajaran yang dipilih."
                />
            </div>
            <div v-else class="space-y-4">
                <div
                    v-for="lesson in lessons"
                    :key="lesson.id"
                    class="bg-surface rounded-card border border-line shadow-card overflow-hidden"
                >
                    <div class="p-4 bg-surface-raised border-b border-line flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <span class="px-2 py-0.5 rounded-control bg-brand-50 text-brand-700 text-[10px] font-bold uppercase">
                                {{ lesson.subject_name }}
                            </span>
                            <h4 class="text-sm font-bold text-ink mt-1">{{ lesson.title }}</h4>
                            <p class="text-[11px] text-ink-muted mt-0.5">Guru: {{ lesson.teacher_name }}</p>
                        </div>
                        <a
                            v-if="lesson.has_attachment"
                            :href="route('siswa.lms.attachment', lesson.id)"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-600 hover:text-brand-700 hover:underline shrink-0"
                        >
                            <span>📎 Unduh Materi</span>
                        </a>
                    </div>

                    <div v-if="lesson.content" class="p-4 text-xs text-ink-muted leading-relaxed whitespace-pre-line border-b border-line">
                        {{ lesson.content }}
                    </div>

                    <!-- Assignments list within lesson -->
                    <div v-if="lesson.assignments && lesson.assignments.length > 0" class="p-4 space-y-3 bg-surface">
                        <h5 class="text-xs font-bold uppercase tracking-wider text-ink-muted">Tugas & Latihan Terkait:</h5>
                        <div
                            v-for="assignment in lesson.assignments"
                            :key="assignment.id"
                            class="p-3.5 rounded-control bg-surface-raised border border-line flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                        >
                            <div class="min-w-0">
                                <h6 class="text-xs font-bold text-ink truncate">{{ assignment.title }}</h6>
                                <p class="text-[11px] text-ink-muted mt-0.5">Batas Pengumpulan: {{ assignment.due_date }}</p>
                                <p v-if="assignment.instructions" class="text-xs text-ink-muted mt-1 leading-relaxed">
                                    {{ assignment.instructions }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2 shrink-0 self-start sm:self-auto">
                                <span
                                    v-if="assignment.submission"
                                    class="px-2.5 py-1 rounded-control text-xs font-bold bg-status-success-bg text-status-success-fg"
                                >
                                    {{ assignment.submission.score !== null ? `Nilai: ${assignment.submission.score}` : 'Terkumpul' }}
                                </span>
                                <AppButton
                                    size="sm"
                                    :variant="assignment.submission ? 'secondary' : 'primary'"
                                    @click="openSubmitModal(assignment)"
                                >
                                    {{ assignment.submission ? 'Ubah Kiriman' : 'Kumpulkan' }}
                                </AppButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Modal -->
        <AssignmentSubmitModal
            :show="showSubmitModal"
            :assignment="activeAssignment"
            @close="showSubmitModal = false"
        />
    </PortalShell>
</template>
