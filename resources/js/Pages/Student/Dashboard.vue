<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
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
</script>

<template>
    <Head title="Dashboard Siswa" />

    <StudentLayout>
        <template #header>
            <div>
                <h1 class="text-base sm:text-lg font-extrabold text-slate-800">Beranda Siswa</h1>
                <p class="text-xs text-slate-500">SD Muhammadiyah Al Mujahidin Wonosari</p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Hero Student Card -->
            <div class="rounded-2xl bg-gradient-to-r from-sky-600 via-teal-600 to-emerald-600 p-5 sm:p-6 text-white shadow-lg">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <span class="inline-block px-3 py-1 rounded-full bg-white/20 text-xs font-bold tracking-wide mb-2">
                            {{ todayName }}, {{ todayDate }}
                        </span>
                        <h2 class="text-xl sm:text-2xl font-black">Assalamu'alaikum, {{ student.name }}</h2>
                        <p class="text-sm text-sky-100 mt-1">
                            Kelas {{ student.class_name }} ({{ student.tingkat }}) &bull; NISN {{ student.nisn }}
                        </p>
                        <p class="text-xs text-sky-100/90 mt-1">Wali Kelas: {{ student.wali_kelas }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <Link
                            :href="route('siswa.lms')"
                            class="px-4 py-2 rounded-xl bg-white text-teal-800 text-xs sm:text-sm font-bold shadow hover:bg-sky-50 transition"
                        >
                            Buka LMS & Tugas
                        </Link>
                        <Link
                            :href="route('siswa.cbt')"
                            class="px-4 py-2 rounded-xl bg-amber-400 text-slate-900 text-xs sm:text-sm font-bold shadow hover:bg-amber-300 transition"
                        >
                            Ujian CBT
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Mobile-First Quick Stats Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
                    <p class="text-xs font-semibold text-slate-500">Tugas Belum Selesai</p>
                    <p class="text-2xl font-black text-amber-600 mt-1">{{ summary.pending_tasks }}</p>
                    <Link :href="route('siswa.lms')" class="text-[11px] font-bold text-sky-600 hover:underline mt-2 inline-block">
                        Lihat Tugas &rarr;
                    </Link>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
                    <p class="text-xs font-semibold text-slate-500">Ujian CBT Aktif</p>
                    <p class="text-2xl font-black text-indigo-600 mt-1">{{ summary.active_exams_count }}</p>
                    <Link :href="route('siswa.cbt')" class="text-[11px] font-bold text-sky-600 hover:underline mt-2 inline-block">
                        Daftar Ujian &rarr;
                    </Link>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
                    <p class="text-xs font-semibold text-slate-500">Kehadiran Saya</p>
                    <p class="text-2xl font-black text-emerald-600 mt-1">{{ summary.attendance_rate }}%</p>
                    <Link :href="route('siswa.presensi')" class="text-[11px] font-bold text-sky-600 hover:underline mt-2 inline-block">
                        Riwayat Presensi &rarr;
                    </Link>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
                    <p class="text-xs font-semibold text-slate-500">Poin Pelanggaran</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <p class="text-2xl font-black text-slate-800">{{ summary.violation_points }}</p>
                        <span
                            class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                            :class="summary.sp_status === 'Aman' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'"
                        >
                            {{ summary.sp_status }}
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">
                        Rata-rata nilai: {{ summary.average_grade ?? '-' }}
                    </p>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Jadwal Hari Ini -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-extrabold text-slate-800">Jadwal Hari Ini ({{ todayName }})</h3>
                        <Link :href="route('siswa.jadwal')" class="text-xs font-bold text-sky-600 hover:underline">
                            Semua Jadwal
                        </Link>
                    </div>
                    <div v-if="todaySchedules.length === 0" class="text-sm text-slate-500 py-6 text-center bg-slate-50 rounded-xl">
                        Tidak ada jadwal pelajaran untuk hari ini.
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="item in todaySchedules"
                            :key="item.id"
                            class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 border border-slate-100"
                        >
                            <div>
                                <p class="font-bold text-sm text-slate-800">{{ item.subject }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ item.teacher }} &bull; {{ item.room }}</p>
                            </div>
                            <div class="text-right shrink-0 ml-3">
                                <span class="inline-block px-2.5 py-1 rounded-lg bg-sky-100 text-sky-800 text-xs font-bold">
                                    {{ item.time_start }} - {{ item.time_end }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tugas Mendekati Deadline -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-extrabold text-slate-800">Tugas Mendekati Deadline</h3>
                        <Link :href="route('siswa.lms')" class="text-xs font-bold text-sky-600 hover:underline">
                            Buka LMS
                        </Link>
                    </div>
                    <div v-if="upcomingAssignments.length === 0" class="text-sm text-slate-500 py-6 text-center bg-slate-50 rounded-xl">
                        Belum ada tugas aktif saat ini.
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="task in upcomingAssignments"
                            :key="task.id"
                            class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-3"
                        >
                            <div class="min-w-0">
                                <p class="font-bold text-sm text-slate-800 truncate">{{ task.title }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ task.subject }} &bull; Batas: {{ task.due_date }}</p>
                            </div>
                            <span
                                v-if="task.is_submitted"
                                class="shrink-0 px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-bold"
                            >
                                {{ task.score !== null ? `Nilai ${task.score}` : 'Terkumpul' }}
                            </span>
                            <span
                                v-else
                                class="shrink-0 px-2.5 py-1 rounded-lg text-xs font-bold"
                                :class="task.is_overdue ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-800'"
                            >
                                {{ task.is_overdue ? 'Terlewat' : 'Belum Kumpul' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Materi Mingguan (Ringkasan Jurnal Guru) -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-extrabold text-slate-800">Materi Mingguan</h3>
                            <p class="text-[11px] text-slate-500">Ringkasan jurnal guru ({{ weekLabel || 'Minggu Ini' }})</p>
                        </div>
                        <Link :href="route('siswa.materi-mingguan')" class="text-xs font-bold text-sky-600 hover:underline">
                            Lihat Semua
                        </Link>
                    </div>
                    <div v-if="weeklyMaterials.length === 0" class="text-sm text-slate-500 py-6 text-center bg-slate-50 rounded-xl">
                        Belum ada ringkasan jurnal guru pada minggu ini.
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="item in weeklyMaterials"
                            :key="item.id"
                            class="p-3.5 rounded-xl bg-teal-50/60 border border-teal-100 space-y-1"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-2 py-0.5 rounded bg-teal-600 text-white text-[10px] font-bold">
                                    {{ item.subject }}
                                </span>
                                <span class="text-[11px] text-slate-500">{{ item.date }}</span>
                            </div>
                            <p class="font-bold text-sm text-slate-800">{{ item.topic }}</p>
                            <p class="text-xs text-slate-600 line-clamp-2">{{ item.summary }}</p>
                        </div>
                    </div>
                </div>

                <!-- Ujian CBT Aktif -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-extrabold text-slate-800">Ujian Online (CBT)</h3>
                        <Link :href="route('siswa.cbt')" class="text-xs font-bold text-sky-600 hover:underline">
                            Lihat Semua
                        </Link>
                    </div>
                    <div v-if="activeExams.length === 0" class="text-sm text-slate-500 py-6 text-center bg-slate-50 rounded-xl">
                        Tidak ada ujian CBT aktif saat ini.
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="exam in activeExams"
                            :key="exam.id"
                            class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-3"
                        >
                            <div class="min-w-0">
                                <p class="font-bold text-sm text-slate-800 truncate">{{ exam.title }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    {{ exam.subject }} &bull; {{ exam.duration_minutes }} menit &bull; {{ exam.questions_count }} soal
                                </p>
                            </div>
                            <Link
                                v-if="exam.can_start"
                                :href="route('siswa.cbt.start', exam.id)"
                                class="shrink-0 px-3 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700"
                            >
                                Kerjakan
                            </Link>
                            <span
                                v-else-if="exam.is_finished"
                                class="shrink-0 px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-bold"
                            >
                                Nilai: {{ exam.score ?? 0 }}
                            </span>
                            <span v-else class="shrink-0 px-2.5 py-1 rounded-lg bg-slate-200 text-slate-700 text-xs font-bold">
                                Terjadwal
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>

