<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import Tabs from '@/Components/ui/Tabs.vue';
import ProgressBar from '@/Components/ui/ProgressBar.vue';
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
            is_enabled: true,
            total_xp: 0,
            total_submissions: 0,
            level: { level: 1, title: 'Penuntut Ilmu Pemula', icon: '🌱', progress_pct: 0, next_points: 150 },
            badges: [],
            unlocked_count: 0,
            total_badges: 0,
            recent_events: [],
            leaderboard: [],
        }),
    },
});

const activeTab = ref('materi');
const subjectFilter = ref(props.selectedSubjectId || '');
const activeAssignment = ref(null);
const showSubmitModal = ref(false);

const tabs = computed(() => {
    if (!props.gamification?.is_enabled) {
        return [{ id: 'materi', label: 'Materi & Tugas' }];
    }
    return [
        { id: 'materi', label: 'Materi & Tugas' },
        {
            id: 'lencana',
            label: 'Lencana Saya',
            badge: `${props.gamification?.unlocked_count || 0}/${props.gamification?.total_badges || 0}`,
        },
        { id: 'peringkat', label: 'Peringkat Kelas' },
    ];
});

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
                :description="`Kelas ${student.class_name} • Ruang Belajar Mandiri & Apresiasi Prestasi`"
            />
        </template>

        <div class="space-y-4">
            <!-- 1. Gamification Progress Card (Tampil jika gamifikasi aktif) -->
            <div
                v-if="gamification.is_enabled"
                class="rounded-card border border-brand-500/30 p-4 sm:p-5 shadow-card bg-surface relative overflow-hidden"
            >
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-14 h-14 rounded-control bg-brand-50 border border-brand-200 flex items-center justify-center text-3xl shadow-inner shrink-0">
                            {{ gamification.level?.icon || '🌱' }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full bg-brand-600 text-brand-fg font-extrabold text-[10px] tracking-wide uppercase">
                                    Level {{ gamification.level?.level || 1 }}
                                </span>
                                <h3 class="text-sm sm:text-base font-black text-ink">
                                    {{ gamification.level?.title || 'Penuntut Ilmu' }}
                                </h3>
                            </div>
                            <p class="text-xs text-ink-muted mt-1">
                                Total Perolehan: <strong class="text-brand-600 font-bold font-mono">{{ gamification.total_xp }} Poin Prestasi</strong> &bull; {{ gamification.unlocked_count }} Lencana Diraih
                            </p>
                        </div>
                    </div>

                    <!-- Progress Bar Ring Menuju Level Berikutnya -->
                    <div class="w-full sm:w-64">
                        <div class="flex items-center justify-between text-xs text-ink-muted mb-1 font-medium">
                            <span>Kemajuan Level</span>
                            <span class="font-bold text-brand-700">{{ gamification.level?.progress_pct || 0 }}%</span>
                        </div>
                        <ProgressBar
                            :value="gamification.level?.progress_pct || 0"
                            color="brand"
                            height="h-2.5"
                        />
                        <p class="text-[10px] text-ink-muted text-right mt-1">
                            Target Level Berikutnya: {{ gamification.level?.next_points }} Poin
                        </p>
                    </div>
                </div>
            </div>

            <!-- 2. Navigasi Tab -->
            <Tabs
                v-if="gamification.is_enabled"
                v-model="activeTab"
                :tabs="tabs"
                variant="pills"
            />

            <!-- ================= TAB 1: MATERI & TUGAS ================= -->
            <div v-show="activeTab === 'materi'" class="space-y-4">
                <!-- Subject Filter -->
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

                <!-- Lessons List -->
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
                                    {{ lesson.subject }}
                                </span>
                                <h4 class="text-sm font-bold text-ink mt-1">{{ lesson.title }}</h4>
                                <p class="text-[11px] text-ink-muted mt-0.5">Guru: {{ lesson.teacher }}</p>
                            </div>
                            <a
                                v-if="lesson.has_downloadable_file && lesson.attachment_url"
                                :href="lesson.attachment_url"
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
                                    <p v-if="assignment.description" class="text-xs text-ink-muted mt-1 leading-relaxed">
                                        {{ assignment.description }}
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

            <!-- ================= TAB 2: LENCANA SAYA ================= -->
            <div v-show="activeTab === 'lencana'" class="space-y-4">
                <div class="bg-surface rounded-card border border-line p-4 shadow-card">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-sm font-bold text-ink">Koleksi Lencana Prestasi</h4>
                            <p class="text-xs text-ink-muted">Kumpulkan lencana dengan giat belajar, mengumpulkan tugas, dan berakhlak mulia.</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-brand-50 border border-brand-200 text-brand-800 text-xs font-bold">
                            {{ gamification.unlocked_count }} / {{ gamification.total_badges }} Terbuka
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        <div
                            v-for="badge in gamification.badges"
                            :key="badge.id"
                            class="p-4 rounded-control border transition-all flex items-start gap-3.5 relative overflow-hidden"
                            :class="badge.is_unlocked
                                ? 'bg-surface-raised border-brand-300 shadow-sm'
                                : 'bg-surface-sunken border-line opacity-60 grayscale'"
                        >
                            <div class="w-12 h-12 rounded-control flex items-center justify-center text-2xl shrink-0"
                                :class="badge.is_unlocked ? 'bg-brand-50 border border-brand-200' : 'bg-surface border border-line'"
                            >
                                {{ badge.icon }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <h5 class="text-xs font-bold text-ink truncate">{{ badge.name }}</h5>
                                    <span
                                        class="text-[9px] px-1.5 py-0.5 rounded font-bold uppercase"
                                        :class="badge.is_unlocked ? 'bg-status-success-bg text-status-success-fg' : 'bg-surface text-ink-muted'"
                                    >
                                        {{ badge.is_unlocked ? 'Diraih' : 'Terkunci' }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-ink-muted mt-1 leading-snug line-clamp-2">
                                    {{ badge.description }}
                                </p>
                                <p v-if="badge.is_unlocked && badge.awarded_at" class="text-[10px] text-brand-700 font-semibold mt-2">
                                    Dibuka pada: {{ badge.awarded_at }}
                                </p>
                                <p v-else-if="badge.points_required > 0" class="text-[10px] text-ink-muted mt-2">
                                    Syarat: {{ badge.points_required }} Poin
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Perolehan Poin Terakhir -->
                <div v-if="gamification.recent_events && gamification.recent_events.length > 0" class="bg-surface rounded-card border border-line p-4 shadow-card">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-ink-muted mb-3">Aktivitas Perolehan Poin Terakhir:</h4>
                    <div class="divide-y divide-line">
                        <div
                            v-for="event in gamification.recent_events"
                            :key="event.id"
                            class="py-2.5 flex items-center justify-between gap-3 text-xs"
                        >
                            <div>
                                <p class="font-bold text-ink">{{ event.notes }}</p>
                                <span class="text-[10px] text-ink-muted">{{ event.created_at }}</span>
                            </div>
                            <span class="font-black font-mono text-status-success-fg bg-status-success-bg px-2 py-0.5 rounded-control">
                                +{{ event.points }} Poin
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= TAB 3: PERINGKAT KELAS (LEADERBOARD) ================= -->
            <div v-show="activeTab === 'peringkat'" class="space-y-4">
                <div class="bg-surface rounded-card border border-line p-4 shadow-card">
                    <div class="mb-4">
                        <h4 class="text-sm font-bold text-ink">Papan Peringkat Sahabat Kelas</h4>
                        <p class="text-xs text-ink-muted">Peringkat apresiasi keaktifan dan semangat belajar peserta didik kelas {{ student.class_name }}.</p>
                    </div>

                    <div v-if="!gamification.leaderboard || gamification.leaderboard.length === 0">
                        <EmptyState
                            title="Belum Ada Peringkat"
                            description="Poin sahabat kelas akan muncul setelah siswa aktif mengumpulkan tugas atau ujian."
                        />
                    </div>
                    <div v-else class="divide-y divide-line">
                        <div
                            v-for="item in gamification.leaderboard"
                            :key="item.student_id"
                            class="py-3 px-2 flex items-center justify-between gap-3 transition-colors"
                            :class="item.student_id === student.id ? 'bg-brand-50/50 rounded-control font-bold' : ''"
                        >
                            <div class="flex items-center gap-3">
                                <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black shrink-0"
                                    :class="item.rank <= 3 ? 'bg-brand-100 text-brand-800' : 'bg-surface-sunken text-ink-muted'"
                                >
                                    {{ item.medal || item.rank }}
                                </span>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <p class="text-xs sm:text-sm font-bold text-ink">{{ item.name }}</p>
                                        <span v-if="item.student_id === student.id" class="px-1.5 py-0.5 rounded text-[9px] bg-brand-600 text-brand-fg uppercase">
                                            Saya
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-ink-muted">{{ item.class_name }} &bull; {{ item.badges_count }} Lencana</p>
                                </div>
                            </div>

                            <div class="text-right">
                                <span class="text-xs sm:text-sm font-black font-mono text-brand-700">
                                    {{ item.total_points }}
                                </span>
                                <span class="text-[10px] text-ink-muted block">Poin</span>
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
