<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import Avatar from '@/Components/ui/Avatar.vue';

const props = defineProps({
    student: { type: Object, required: true },
    todayName: { type: String, required: true },
    todayDate: { type: String, required: true },
    weekLabel: { type: String, default: '' },
    todaySchedules: { type: Array, default: () => [] },
    upcomingAssignments: { type: Array, default: () => [] },
    activeExams: { type: Array, default: () => [] },
    weeklyMaterials: { type: Array, default: () => [] },
    summary: { type: Object, required: true },
});

// Mode Ramah Anak untuk tingkat 1, 2, 3
const isKidsMode = computed(() => {
    const tingkat = String(props.student?.tingkat || '');
    return ['1', '2', '3'].some((l) => tingkat.includes(l));
});

// Next lesson today
const nextLesson = computed(() => props.todaySchedules[0] || null);

// Closest assignment
const closestTask = computed(() => props.upcomingAssignments.find((t) => !t.is_submitted) || props.upcomingAssignments[0] || null);
</script>

<template>
    <Head title="Beranda Siswa" />

    <PortalShell variant="bottom-nav">
        <template #header>
            <PageHeader
                title="Beranda Siswa"
                :description="`SD Muhammadiyah Al Mujahidin • Kelas ${student.class_name}`"
            />
        </template>

        <div class="space-y-5">
            <!-- 1. Hero Greeting Banner (With Kid-Friendly Mode adjustments) -->
            <div
                class="rounded-card border border-brand-500/30 p-5 sm:p-6 shadow-card bg-surface relative overflow-hidden"
                :class="isKidsMode ? 'bg-gradient-to-r from-brand-50 via-surface to-brand-50/50' : 'bg-surface'"
            >
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative z-10">
                    <div class="flex items-start sm:items-center gap-3.5 min-w-0">
                        <Avatar
                            :name="student.name"
                            :size="isKidsMode ? 'xl' : 'lg'"
                            class="ring-2 ring-brand-500/40 shrink-0"
                        />
                        <div class="min-w-0">
                            <span class="inline-block px-2.5 py-0.5 rounded-full bg-brand-100 text-brand-800 text-[11px] font-bold tracking-wide mb-1">
                                {{ todayName }}, {{ todayDate }}
                            </span>
                            <h2
                                class="font-black text-ink truncate"
                                :class="isKidsMode ? 'text-xl sm:text-2xl text-brand-900' : 'text-lg sm:text-xl'"
                            >
                                Assalamu'alaikum, {{ student.name }}!
                            </h2>
                            <p class="text-xs text-ink-muted truncate mt-0.5">
                                Kelas {{ student.class_name }} ({{ student.tingkat }}) &bull; NISN: {{ student.nisn }} &bull; Wali: {{ student.wali_kelas }}
                            </p>
                        </div>
                    </div>

                    <!-- Quick Action Buttons -->
                    <div class="flex items-center gap-2 shrink-0">
                        <Link :href="route('siswa.lms')">
                            <AppButton
                                :size="isKidsMode ? 'lg' : 'sm'"
                                variant="primary"
                            >
                                Buka Tugas LMS
                            </AppButton>
                        </Link>
                        <Link :href="route('siswa.cbt')">
                            <AppButton
                                :size="isKidsMode ? 'lg' : 'sm'"
                                variant="secondary"
                            >
                                Ujian CBT
                            </AppButton>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- 2. Priority Focus: "Pelajaran Berikutnya" & "Tugas Terdekat" -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                <!-- Pelajaran Berikutnya -->
                <div class="bg-surface rounded-card border border-line p-4 shadow-card flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-ink-muted">
                                Pelajaran Berikutnya Hari Ini
                            </span>
                        </div>
                        <div v-if="nextLesson" class="mt-2">
                            <h4 class="text-base font-bold text-ink truncate">{{ nextLesson.subject }}</h4>
                            <p class="text-xs text-ink-muted mt-0.5">
                                {{ nextLesson.teacher }} &bull; Ruang: {{ nextLesson.room }} ({{ nextLesson.jpl }} JPL)
                            </p>
                            <span class="inline-block mt-2 px-2.5 py-0.5 rounded-control bg-brand-50 text-brand-700 text-xs font-mono font-bold">
                                {{ nextLesson.time_start }} - {{ nextLesson.time_end }}
                            </span>
                        </div>
                        <div v-else class="mt-2 text-xs text-ink-muted">
                            Tidak ada jadwal pelajaran lagi hari ini.
                        </div>
                    </div>
                    <Link :href="route('siswa.jadwal')" class="text-xs font-bold text-brand-600 hover:underline shrink-0">
                        Jadwal &rarr;
                    </Link>
                </div>

                <!-- Tugas Terdekat -->
                <div class="bg-surface rounded-card border border-line p-4 shadow-card flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-status-warning-fg"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-ink-muted">
                                Tugas Terdekat
                            </span>
                        </div>
                        <div v-if="closestTask" class="mt-2">
                            <h4 class="text-base font-bold text-ink truncate">{{ closestTask.title }}</h4>
                            <p class="text-xs text-ink-muted mt-0.5">{{ closestTask.subject }} &bull; Batas: {{ closestTask.due_date }}</p>
                            <div class="mt-2">
                                <span
                                    v-if="closestTask.is_submitted"
                                    class="px-2 py-0.5 rounded-control text-xs font-bold bg-status-success-bg text-status-success-fg"
                                >
                                    Sudah Dikumpulkan
                                </span>
                                <span
                                    v-else
                                    class="px-2 py-0.5 rounded-control text-xs font-bold"
                                    :class="closestTask.is_overdue ? 'bg-status-danger-bg text-status-danger-fg' : 'bg-status-warning-bg text-status-warning-fg'"
                                >
                                    {{ closestTask.is_overdue ? 'Terlewat Deadline' : 'Perlu Dikerjakan' }}
                                </span>
                            </div>
                        </div>
                        <div v-else class="mt-2 text-xs text-ink-muted">
                            Alhamdulillah, tidak ada tanggungan tugas saat ini.
                        </div>
                    </div>
                    <Link :href="route('siswa.lms')" class="text-xs font-bold text-brand-600 hover:underline shrink-0">
                        LMS &rarr;
                    </Link>
                </div>
            </div>

            <!-- 3. Stat Cards Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-surface rounded-card border border-line p-4 shadow-card">
                    <p class="text-xs font-semibold text-ink-muted">Tugas Belum Selesai</p>
                    <p class="text-2xl font-black text-status-warning-fg mt-1 font-mono">{{ summary.pending_tasks }}</p>
                    <Link :href="route('siswa.lms')" class="text-[11px] font-bold text-brand-600 hover:underline mt-2 inline-block">
                        Lihat Daftar Tugas &rarr;
                    </Link>
                </div>

                <div class="bg-surface rounded-card border border-line p-4 shadow-card">
                    <p class="text-xs font-semibold text-ink-muted">Ujian CBT Aktif</p>
                    <p class="text-2xl font-black text-brand-600 mt-1 font-mono">{{ summary.active_exams_count }}</p>
                    <Link :href="route('siswa.cbt')" class="text-[11px] font-bold text-brand-600 hover:underline mt-2 inline-block">
                        Daftar Ujian &rarr;
                    </Link>
                </div>

                <div class="bg-surface rounded-card border border-line p-4 shadow-card">
                    <p class="text-xs font-semibold text-ink-muted">Kehadiran Saya</p>
                    <p class="text-2xl font-black text-status-success-fg mt-1 font-mono">{{ summary.attendance_rate }}%</p>
                    <Link :href="route('siswa.presensi')" class="text-[11px] font-bold text-brand-600 hover:underline mt-2 inline-block">
                        Riwayat Presensi &rarr;
                    </Link>
                </div>

                <div class="bg-surface rounded-card border border-line p-4 shadow-card">
                    <p class="text-xs font-semibold text-ink-muted">Poin Disiplin</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl font-black text-ink font-mono">{{ summary.violation_points }}</span>
                        <StatusBadge :status="summary.sp_status" />
                    </div>
                    <p class="text-[11px] text-ink-muted mt-1">
                        Rata-rata Nilai: {{ summary.average_grade ?? '-' }}
                    </p>
                </div>
            </div>

            <!-- 4. Content Sections: Jadwal Hari Ini & Tugas & Materi & CBT -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Jadwal Hari Ini -->
                <div class="bg-surface rounded-card border border-line shadow-card p-4 space-y-3">
                    <div class="flex items-center justify-between border-b border-line pb-2.5">
                        <h3 class="font-bold text-sm text-ink">Jadwal Hari Ini ({{ todayName }})</h3>
                        <Link :href="route('siswa.jadwal')" class="text-xs font-bold text-brand-600 hover:underline">
                            Semua Jadwal &rarr;
                        </Link>
                    </div>

                    <div v-if="todaySchedules.length === 0">
                        <EmptyState
                            title="Tidak Ada Jadwal"
                            description="Tidak ada kegiatan pelajaran terjadwal untuk hari ini."
                        />
                    </div>
                    <div v-else class="space-y-2">
                        <div
                            v-for="item in todaySchedules"
                            :key="item.id"
                            class="p-3 rounded-control bg-surface-raised border border-line flex items-center justify-between gap-3"
                        >
                            <div class="min-w-0">
                                <p class="font-bold text-xs text-ink truncate">{{ item.subject }}</p>
                                <p class="text-[11px] text-ink-muted mt-0.5">{{ item.teacher }} &bull; {{ item.room }}</p>
                            </div>
                            <span class="shrink-0 px-2 py-0.5 rounded-control bg-brand-50 text-brand-700 text-xs font-mono font-bold">
                                {{ item.time_start }} - {{ item.time_end }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tugas Mendekati Deadline -->
                <div class="bg-surface rounded-card border border-line shadow-card p-4 space-y-3">
                    <div class="flex items-center justify-between border-b border-line pb-2.5">
                        <h3 class="font-bold text-sm text-ink">Tugas Mendekati Deadline</h3>
                        <Link :href="route('siswa.lms')" class="text-xs font-bold text-brand-600 hover:underline">
                            Buka LMS &rarr;
                        </Link>
                    </div>

                    <div v-if="upcomingAssignments.length === 0">
                        <EmptyState
                            title="Belum Ada Tugas"
                            description="Belum ada penugasan aktif dari guru."
                        />
                    </div>
                    <div v-else class="space-y-2">
                        <div
                            v-for="task in upcomingAssignments"
                            :key="task.id"
                            class="p-3 rounded-control bg-surface-raised border border-line flex items-center justify-between gap-3"
                        >
                            <div class="min-w-0">
                                <p class="font-bold text-xs text-ink truncate">{{ task.title }}</p>
                                <p class="text-[11px] text-ink-muted mt-0.5">{{ task.subject }} &bull; Batas: {{ task.due_date }}</p>
                            </div>
                            <span
                                v-if="task.is_submitted"
                                class="shrink-0 px-2 py-0.5 rounded-control bg-status-success-bg text-status-success-fg text-xs font-bold"
                            >
                                {{ task.score !== null ? `Nilai ${task.score}` : 'Terkumpul' }}
                            </span>
                            <span
                                v-else
                                class="shrink-0 px-2 py-0.5 rounded-control text-xs font-bold"
                                :class="task.is_overdue ? 'bg-status-danger-bg text-status-danger-fg' : 'bg-status-warning-bg text-status-warning-fg'"
                            >
                                {{ task.is_overdue ? 'Terlewat' : 'Belum Kumpul' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Materi Mingguan -->
                <div class="bg-surface rounded-card border border-line shadow-card p-4 space-y-3">
                    <div class="flex items-center justify-between border-b border-line pb-2.5">
                        <div>
                            <h3 class="font-bold text-sm text-ink">Materi Mingguan Guru</h3>
                            <p class="text-[11px] text-ink-muted">{{ weekLabel || 'Minggu Ini' }}</p>
                        </div>
                        <Link :href="route('siswa.materi-mingguan')" class="text-xs font-bold text-brand-600 hover:underline">
                            Lihat Semua &rarr;
                        </Link>
                    </div>

                    <div v-if="weeklyMaterials.length === 0">
                        <EmptyState
                            title="Belum Ada Materi"
                            description="Belum ada ringkasan materi jurnal guru pada minggu ini."
                        />
                    </div>
                    <div v-else class="space-y-2">
                        <div
                            v-for="item in weeklyMaterials"
                            :key="item.id"
                            class="p-3 rounded-control bg-surface-raised border border-line space-y-1"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-2 py-0.5 rounded-control bg-brand-50 text-brand-700 text-[10px] font-bold">
                                    {{ item.subject }}
                                </span>
                                <span class="text-[11px] text-ink-muted">{{ item.date }}</span>
                            </div>
                            <p class="font-bold text-xs text-ink">{{ item.topic }}</p>
                            <p class="text-xs text-ink-muted line-clamp-2 leading-relaxed">{{ item.summary }}</p>
                        </div>
                    </div>
                </div>

                <!-- Ujian CBT Aktif -->
                <div class="bg-surface rounded-card border border-line shadow-card p-4 space-y-3">
                    <div class="flex items-center justify-between border-b border-line pb-2.5">
                        <h3 class="font-bold text-sm text-ink">Ujian Online (CBT)</h3>
                        <Link :href="route('siswa.cbt')" class="text-xs font-bold text-brand-600 hover:underline">
                            Lihat Semua &rarr;
                        </Link>
                    </div>

                    <div v-if="activeExams.length === 0">
                        <EmptyState
                            title="Tidak Ada Ujian Aktif"
                            description="Tidak ada ujian online CBT yang sedang berlangsung saat ini."
                        />
                    </div>
                    <div v-else class="space-y-2">
                        <div
                            v-for="exam in activeExams"
                            :key="exam.id"
                            class="p-3 rounded-control bg-surface-raised border border-line flex items-center justify-between gap-3"
                        >
                            <div class="min-w-0">
                                <p class="font-bold text-xs text-ink truncate">{{ exam.title }}</p>
                                <p class="text-[11px] text-ink-muted mt-0.5">
                                    {{ exam.subject }} &bull; {{ exam.duration_minutes }} menit &bull; {{ exam.questions_count }} butir
                                </p>
                            </div>
                            <Link v-if="exam.can_start" :href="route('siswa.cbt.start', exam.id)">
                                <AppButton size="sm" variant="primary">
                                    Kerjakan
                                </AppButton>
                            </Link>
                            <span
                                v-else-if="exam.is_finished"
                                class="shrink-0 px-2.5 py-1 rounded-control bg-status-success-bg text-status-success-fg text-xs font-bold"
                            >
                                Nilai: {{ exam.score ?? 0 }}
                            </span>
                            <span v-else class="shrink-0 px-2 py-0.5 rounded-control bg-surface text-ink-muted border border-line text-xs font-bold">
                                Terjadwal
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalShell>
</template>
