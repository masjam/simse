<script setup>
import TeacherLayout from '@/Layouts/TeacherLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

// Props from Controller
const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    todaySchedules: { type: Array, default: () => [] },
    pendingTasks: { type: Array, default: () => [] },
    recentActivities: { type: Array, default: () => [] },
});

// Demo data fallback
const stats = computed(() => props.stats?.totalClasses !== undefined ? props.stats : {
    totalStudents: 60,
    totalClasses: 2,
    totalSubjects: 2,
    attendanceRate: 96.5,
    pendingGrades: 1,
    upcomingExams: 1,
});

const todaySchedules = computed(() => props.todaySchedules?.length ? props.todaySchedules : [
    { id: 1, time: '07:30 - 09:00', class: '11 RPL 1', subject: 'Pemrograman Web', room: 'Lab Komputer 1', status: 'done', students: 32 },
    { id: 2, time: '09:30 - 11:00', class: '10 TKJ 2', subject: 'Sistem Operasi', room: 'Ruang Teori 4', status: 'active', students: 30 },
    { id: 3, time: '13:00 - 14:30', class: '12 MM 1', subject: 'Desain Grafis', room: 'Lab Studio', status: 'upcoming', students: 28 },
    { id: 4, time: '14:30 - 16:00', class: '11 TKJ 1', subject: 'Jaringan Komputer', room: 'Lab Jarkom', status: 'upcoming', students: 31 },
]);

const pendingTasks = computed(() => props.pendingTasks?.length ? props.pendingTasks : [
    { id: 1, title: 'Koreksi Esai Ujian PAS - 12 MM 1', deadline: 'Besok, 12:00', priority: 'tinggi', type: 'koreksi', count: 28 },
    { id: 2, title: 'Input Nilai Tugas Harian - 11 RPL 1', deadline: '2 Hari lagi', priority: 'sedang', type: 'nilai', count: 32 },
    { id: 3, title: 'Upload Materi Bab 5 - Sistem Operasi', deadline: '3 Hari lagi', priority: 'rendah', type: 'materi', count: 1 },
]);

const recentActivities = computed(() => props.recentActivities?.length ? props.recentActivities : [
    { id: 1, text: 'Absensi kelas 11 RPL 1 berhasil disimpan', time: '2 jam lalu', icon: 'check', color: 'emerald' },
    { id: 2, text: 'Tugas "Membuat REST API" dikumpulkan 15 siswa', time: '3 jam lalu', icon: 'upload', color: 'blue' },
    { id: 3, text: 'Ujian "UTS Pemrograman Web" dijadwalkan', time: '5 jam lalu', icon: 'calendar', color: 'purple' },
    { id: 4, text: 'Nilai Kuis Bab 4 telah dipublikasikan', time: 'Kemarin', icon: 'star', color: 'amber' },
]);

const currentTime = ref(new Date());
onMounted(() => {
    setInterval(() => currentTime.value = new Date(), 1000);
});

const greeting = computed(() => {
    const h = currentTime.value.getHours();
    if (h < 10) return 'Selamat Pagi';
    if (h < 15) return 'Selamat Siang';
    if (h < 18) return 'Selamat Sore';
    return 'Selamat Malam';
});

const priorityColors = {
    tinggi: 'text-red-700 bg-red-50 border-red-200',
    sedang: 'text-amber-700 bg-amber-50 border-amber-200',
    rendah: 'text-blue-700 bg-blue-50 border-blue-200',
};

const statusColors = {
    done: 'bg-emerald-100 text-emerald-700',
    active: 'bg-indigo-100 text-indigo-700',
    upcoming: 'bg-slate-100 text-slate-500',
};
const statusLabels = {
    done: 'Selesai',
    active: 'Sedang Berlangsung',
    upcoming: 'Mendatang',
};

const activityIcons = {
    check: 'M5 13l4 4L19 7',
    upload: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12',
    calendar: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    star: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
};

const quickMenus = [
    { name: 'Absensi', desc: 'Isi kehadiran kelas', route: 'guru.absensi', icon: 'clipboard-check', gradient: 'from-indigo-500 to-indigo-600' },
    { name: 'Input Nilai', desc: 'Kelola nilai siswa', route: 'guru.nilai', icon: 'chart-bar', gradient: 'from-emerald-500 to-teal-600' },
    { name: 'Buat Ujian', desc: 'Ujian online CBT', route: 'guru.cbt', icon: 'puzzle', gradient: 'from-amber-500 to-orange-600' },
    { name: 'Upload Materi', desc: 'Bahan ajar LMS', route: 'guru.lms', icon: 'book-open', gradient: 'from-purple-500 to-pink-600' },
    { name: 'Tugas', desc: 'Tugas & koreksi', route: 'guru.tugas', icon: 'pencil-alt', gradient: 'from-cyan-500 to-blue-600' },
    { name: 'Lapor BK', desc: 'Catatan pelanggaran', route: 'guru.bk', icon: 'heart', gradient: 'from-rose-500 to-red-600' },
];

const getIcon = (icon) => {
    const icons = {
        'clipboard-check': 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
        'chart-bar': 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        'puzzle': 'M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z',
        'book-open': 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        'pencil-alt': 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        'heart': 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
    };
    return icons[icon] || '';
};
</script>

<template>
    <Head title="Dashboard Guru" />
    <TeacherLayout>
        <template #header-title>
            <h1 class="text-lg font-bold text-slate-800">Dashboard</h1>
        </template>
        <template #header-subtitle>
            <p class="text-xs text-slate-500">Ringkasan aktivitas mengajar Anda hari ini</p>
        </template>

        <div class="space-y-6">
            <!-- Welcome Banner -->
            <div class="relative bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 rounded-2xl p-6 sm:p-8 text-white overflow-hidden shadow-xl shadow-indigo-600/20">
                <div class="absolute -right-12 -top-12 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
                <div class="absolute -left-8 -bottom-8 w-32 h-32 bg-purple-400/20 rounded-full blur-2xl"></div>
                <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <p class="text-indigo-200 text-sm font-medium mb-1">{{ greeting }}, Guru! 👋</p>
                        <h2 class="text-2xl sm:text-3xl font-bold">Siap Mengajar Hari Ini?</h2>
                        <p class="text-indigo-200 mt-2 text-sm">Anda memiliki <strong class="text-white">{{ todaySchedules.length }} jadwal mengajar</strong> dan <strong class="text-white">{{ pendingTasks.length }} tugas pending</strong>.</p>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="text-3xl sm:text-4xl font-bold font-mono tracking-tight">
                            {{ currentTime.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                        </div>
                        <div class="text-indigo-200 text-xs mt-1">
                            {{ currentTime.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' }) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
                <div class="bg-white rounded-xl p-4 border border-slate-200/60 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">{{ stats.totalStudents }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Total Siswa</p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-slate-200/60 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">{{ stats.totalClasses }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Kelas Diampu</p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-slate-200/60 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">{{ stats.totalSubjects }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Mata Pelajaran</p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-slate-200/60 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-teal-50 text-teal-600 rounded-lg flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">{{ stats.attendanceRate }}%</p>
                    <p class="text-xs text-slate-500 mt-0.5">Kehadiran</p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-slate-200/60 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">{{ stats.pendingGrades }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Nilai Pending</p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-slate-200/60 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-rose-50 text-rose-600 rounded-lg flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">{{ stats.upcomingExams }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Ujian Mendatang</p>
                </div>
            </div>

            <!-- Quick Action Menu -->
            <div>
                <h3 class="text-sm font-bold text-slate-700 mb-3">Menu Cepat</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    <Link v-for="menu in quickMenus" :key="menu.route" :href="route(menu.route)"
                          class="group bg-white border border-slate-200/60 rounded-xl p-4 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 text-center">
                        <div :class="`w-12 h-12 bg-gradient-to-br ${menu.gradient} mx-auto rounded-xl flex items-center justify-center mb-3 shadow-lg group-hover:scale-110 transition-transform duration-300`">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIcon(menu.icon)"></path></svg>
                        </div>
                        <p class="text-sm font-bold text-slate-800">{{ menu.name }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ menu.desc }}</p>
                    </Link>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Teaching Schedule (2/3) -->
                <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <h3 class="font-bold text-slate-800">Jadwal Mengajar Hari Ini</h3>
                        </div>
                        <Link :href="route('guru.jadwal')" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 transition">Lihat Semua →</Link>
                    </div>
                    <div class="p-4 space-y-2">
                        <div v-for="schedule in todaySchedules" :key="schedule.id"
                             class="flex items-center gap-4 p-3 rounded-xl transition-colors"
                             :class="schedule.status === 'active' ? 'bg-indigo-50/50 border border-indigo-200' : 'hover:bg-slate-50 border border-transparent'">
                            <div class="w-20 text-center shrink-0">
                                <p class="text-xs font-bold text-slate-800">{{ schedule.time.split(' - ')[0] }}</p>
                                <p class="text-[10px] text-slate-400">{{ schedule.time.split(' - ')[1] }}</p>
                            </div>
                            <div class="w-1 h-12 rounded-full shrink-0"
                                 :class="schedule.status === 'done' ? 'bg-emerald-400' : schedule.status === 'active' ? 'bg-indigo-500' : 'bg-slate-200'"></div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-slate-800 text-sm truncate">{{ schedule.subject }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ schedule.class }} • {{ schedule.room }} • {{ schedule.students }} siswa</p>
                            </div>
                            <div class="shrink-0 flex items-center gap-2">
                                <span :class="statusColors[schedule.status]" class="text-[10px] font-bold px-2.5 py-1 rounded-full whitespace-nowrap">{{ statusLabels[schedule.status] }}</span>
                                <button v-if="schedule.status === 'active'"
                                        class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition shadow-sm">
                                    Buka
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Tasks (1/3) -->
                <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            </div>
                            <h3 class="font-bold text-slate-800">Tugas Pending</h3>
                        </div>
                        <span class="text-xs font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">{{ pendingTasks.length }}</span>
                    </div>
                    <div class="p-4 space-y-3">
                        <div v-for="task in pendingTasks" :key="task.id"
                             class="p-4 rounded-xl border transition-all hover:shadow-sm cursor-pointer"
                             :class="priorityColors[task.priority]">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="text-sm font-bold leading-tight">{{ task.title }}</h4>
                            </div>
                            <div class="flex justify-between items-center">
                                <p class="text-xs opacity-70">Tenggat: {{ task.deadline }}</p>
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-white/60">{{ task.priority }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                    <div class="w-8 h-8 bg-slate-100 text-slate-600 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="font-bold text-slate-800">Aktivitas Terkini</h3>
                </div>
                <div class="divide-y divide-slate-100">
                    <div v-for="activity in recentActivities" :key="activity.id" class="px-6 py-3 flex items-center gap-4 hover:bg-slate-50 transition">
                        <div :class="`w-8 h-8 bg-${activity.color}-50 text-${activity.color}-600 rounded-full flex items-center justify-center shrink-0`">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="activityIcons[activity.icon]"></path></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-700 truncate">{{ activity.text }}</p>
                        </div>
                        <span class="text-xs text-slate-400 shrink-0">{{ activity.time }}</span>
                    </div>
                </div>
            </div>
        </div>
    </TeacherLayout>
</template>
