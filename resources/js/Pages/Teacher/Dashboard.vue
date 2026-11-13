<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    todaySchedules: { type: Array, default: () => [] },
    pendingTasks: { type: Array, default: () => [] },
    recentActivities: { type: Array, default: () => [] },
});

const stats = computed(() => props.stats?.totalClasses !== undefined ? props.stats : {
    totalStudents: 60,
    totalClasses: 2,
    totalSubjects: 2,
    attendanceRate: 96.5,
    pendingGrades: 1,
    upcomingExams: 1,
});

const todaySchedules = computed(() => props.todaySchedules?.length ? props.todaySchedules : [
    { id: 1, time: '07:30 - 09:00', class: '1 Brilliant', subject: 'Pendidikan Agama Islam', room: 'Ruang Teori 1', status: 'done', students: 30 },
    { id: 2, time: '09:30 - 11:00', class: 'I OLYMPIAD', subject: 'Al-Islam & Kemuhammadiyahan', room: 'Ruang Teori 2', status: 'active', students: 28 },
]);

// Actionable tasks list
const pendingTasks = computed(() => props.pendingTasks?.length ? props.pendingTasks : [
    { id: 1, title: 'Koreksi Tugas Esai PAI - 1 Brilliant', deadline: 'Hari ini, 15:00', priority: 'tinggi', type: 'koreksi', count: 12, route: 'guru.tugas', btnLabel: 'Koreksi Sekarang' },
    { id: 2, title: 'Presensi Siswa Kelas I OLYMPIAD Belum Terisi', deadline: 'Segera', priority: 'tinggi', type: 'presensi', count: 28, route: 'guru.absensi', btnLabel: 'Isi Presensi' },
    { id: 3, title: 'Input Nilai Formatif PAI - Bab 3', deadline: '2 Hari lagi', priority: 'sedang', type: 'nilai', count: 30, route: 'guru.nilai', btnLabel: 'Input Nilai' },
]);

const recentActivities = computed(() => props.recentActivities?.length ? props.recentActivities : [
    { id: 1, text: 'Presensi kelas 1 Brilliant berhasil disimpan', time: '1 jam lalu', status: 'Hadir' },
    { id: 2, text: 'Tugas "Hafalan Surat Pendek" dikumpulkan 18 siswa', time: '3 jam lalu', status: 'Tersedia' },
    { id: 3, text: 'Ujian CBT "Kuis PAI Bab 2" selesai', time: '5 jam lalu', status: 'Selesai' },
]);

const currentTime = ref(new Date());
onMounted(() => {
    const timer = setInterval(() => currentTime.value = new Date(), 1000);
    return () => clearInterval(timer);
});

const greeting = computed(() => {
    const h = currentTime.value.getHours();
    if (h < 10) return 'Selamat Pagi';
    if (h < 15) return 'Selamat Siang';
    if (h < 18) return 'Selamat Sore';
    return 'Selamat Malam';
});

const quickMenus = [
    { name: 'Presensi Siswa', desc: 'Isi kehadiran kelas', route: 'guru.absensi', icon: 'clipboard' },
    { name: 'Input Nilai', desc: 'Kelola nilai & e-Raport', route: 'guru.nilai', icon: 'award' },
    { name: 'Ujian CBT', desc: 'Bank soal & ujian', route: 'guru.cbt', icon: 'cpu' },
    { name: 'Materi LMS', desc: 'Bahan ajar mandiri', route: 'guru.lms', icon: 'book-open' },
    { name: 'Penugasan', desc: 'Tugas & koreksi siswa', route: 'guru.tugas', icon: 'file-text' },
    { name: 'Layanan BK', desc: 'Catatan pembinaan', route: 'guru.bk', icon: 'shield' },
];
</script>

<template>
    <Head title="Dashboard Guru" />

    <PortalShell variant="sidebar">
        <template #header>
            <PageHeader
                title="Dashboard Pendidik"
                :description="`${greeting}, Ustadz / Ustadzah • SD Muhammadiyah Al Mujahidin Wonosari`"
            />
        </template>

        <div class="space-y-5">
            <!-- 1. Stats Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <StatCard title="Total Siswa Didik" :value="stats.totalStudents" unit="Siswa" />
                <StatCard title="Rombel Diampu" :value="stats.totalClasses" unit="Kelas" />
                <StatCard title="Kehadiran Siswa Rata-rata" :value="`${stats.attendanceRate}%`" />
                <StatCard title="Tugas Perlu Koreksi" :value="stats.pendingGrades" unit="Tugas" />
            </div>

            <!-- 2. "Yang Perlu Tindakan" (Daftar Tindakan Nyata Guru) -->
            <div class="bg-surface rounded-card border border-brand-500/30 p-4 sm:p-5 shadow-card space-y-3">
                <div class="flex items-center justify-between border-b border-line pb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-status-warning-fg animate-pulse"></span>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-ink">
                            Yang Perlu Tindakan Guru
                        </h3>
                    </div>
                    <span class="text-xs font-semibold text-ink-muted">
                        {{ pendingTasks.length }} Perlu Perhatian
                    </span>
                </div>

                <div v-if="pendingTasks.length === 0">
                    <EmptyState
                        title="Semua Tugas Bersih"
                        description="Alhamdulillah, tidak ada tanggungan koreksi, presensi kosong, atau tugas yang menunggu."
                    />
                </div>
                <div v-else class="space-y-2.5">
                    <div
                        v-for="task in pendingTasks"
                        :key="task.id"
                        class="p-3.5 rounded-control bg-surface-raised border border-line flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 hover:bg-surface transition-colors"
                    >
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span
                                    class="px-2 py-0.5 rounded-control text-[10px] font-bold uppercase"
                                    :class="task.priority === 'tinggi' ? 'bg-status-danger-bg text-status-danger-fg' : 'bg-status-warning-bg text-status-warning-fg'"
                                >
                                    Prioritas {{ task.priority }}
                                </span>
                                <h4 class="text-xs sm:text-sm font-bold text-ink">{{ task.title }}</h4>
                            </div>
                            <p class="text-xs text-ink-muted">
                                Batas: {{ task.deadline }} &bull; Jumlah: {{ task.count }} data
                            </p>
                        </div>
                        <div class="shrink-0">
                            <Link :href="route(task.route || 'guru.dashboard')">
                                <AppButton size="sm" variant="primary">
                                    {{ task.btnLabel || 'Buka Tindakan' }} &rarr;
                                </AppButton>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Jadwal Mengajar Hari Ini & Aktivitas Terkini -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Jadwal Mengajar Hari Ini -->
                <div class="bg-surface rounded-card border border-line shadow-card p-4 space-y-3">
                    <div class="flex items-center justify-between border-b border-line pb-2.5">
                        <h3 class="font-bold text-sm text-ink">Jadwal Mengajar Hari Ini</h3>
                        <Link :href="route('guru.jadwal')" class="text-xs font-bold text-brand-600 hover:underline">
                            Semua Jadwal &rarr;
                        </Link>
                    </div>

                    <div v-if="todaySchedules.length === 0">
                        <EmptyState
                            title="Tidak Ada Jadwal Hari Ini"
                            description="Tidak ada jam mengajar terjadwal untuk hari ini."
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
                                <p class="text-[11px] text-ink-muted mt-0.5">
                                    Kelas {{ item.class }} &bull; {{ item.room }} &bull; {{ item.students }} Siswa
                                </p>
                            </div>
                            <span class="shrink-0 px-2.5 py-1 rounded-control bg-brand-50 text-brand-700 text-xs font-mono font-bold">
                                {{ item.time }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Aktivitas Terkini -->
                <div class="bg-surface rounded-card border border-line shadow-card p-4 space-y-3">
                    <div class="flex items-center justify-between border-b border-line pb-2.5">
                        <h3 class="font-bold text-sm text-ink">Log Aktivitas Pembelajaran</h3>
                        <span class="text-xs text-ink-muted">Terbaru</span>
                    </div>

                    <div class="space-y-2">
                        <div
                            v-for="act in recentActivities"
                            :key="act.id"
                            class="p-3 rounded-control bg-surface-raised border border-line flex items-center justify-between gap-3"
                        >
                            <div class="min-w-0">
                                <p class="text-xs text-ink font-semibold truncate">{{ act.text }}</p>
                                <p class="text-[11px] text-ink-muted mt-0.5">{{ act.time }}</p>
                            </div>
                            <StatusBadge :status="act.status" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Quick Access Nav Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <Link
                    v-for="menu in quickMenus"
                    :key="menu.name"
                    :href="route(menu.route)"
                    class="bg-surface rounded-card border border-line p-3.5 shadow-card hover:bg-surface-raised hover:border-brand-500/40 transition-all flex flex-col justify-between gap-2 group"
                >
                    <div>
                        <h4 class="text-xs font-bold text-ink group-hover:text-brand-600 transition-colors">
                            {{ menu.name }}
                        </h4>
                        <p class="text-[11px] text-ink-muted mt-0.5 leading-snug">
                            {{ menu.desc }}
                        </p>
                    </div>
                    <span class="text-xs font-bold text-brand-600 group-hover:translate-x-1 transition-transform inline-block">
                        Buka &rarr;
                    </span>
                </Link>
            </div>
        </div>
    </PortalShell>
</template>
