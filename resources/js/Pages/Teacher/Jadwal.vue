<script setup>
import TeacherLayout from '@/Layouts/TeacherLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    schedules: { type: Array, default: () => [] },
});

const selectedDay = ref(new Date().getDay() === 0 ? 1 : new Date().getDay());

const days = [
    { id: 1, name: 'Senin', short: 'Sen' },
    { id: 2, name: 'Selasa', short: 'Sel' },
    { id: 3, name: 'Rabu', short: 'Rab' },
    { id: 4, name: 'Kamis', short: 'Kam' },
    { id: 5, name: 'Jumat', short: 'Jum' },
    { id: 6, name: 'Sabtu', short: 'Sab' },
];

const allSchedules = computed(() => props.schedules?.length ? props.schedules : []);

const filteredSchedules = computed(() => {
    return allSchedules.value.filter(s => (s.dayNumber ? s.dayNumber === selectedDay.value : s.day === selectedDay.value));
});

const totalJam = computed(() => filteredSchedules.value.reduce((acc, s) => acc + (s.jpl || 2), 0));

const colorMap = {
    indigo: { bg: 'bg-indigo-50', border: 'border-indigo-200', text: 'text-indigo-700', dot: 'bg-indigo-500', badge: 'bg-indigo-100 text-indigo-700' },
    emerald: { bg: 'bg-emerald-50', border: 'border-emerald-200', text: 'text-emerald-700', dot: 'bg-emerald-500', badge: 'bg-emerald-100 text-emerald-700' },
    purple: { bg: 'bg-purple-50', border: 'border-purple-200', text: 'text-purple-700', dot: 'bg-purple-500', badge: 'bg-purple-100 text-purple-700' },
    amber: { bg: 'bg-amber-50', border: 'border-amber-200', text: 'text-amber-700', dot: 'bg-amber-500', badge: 'bg-amber-100 text-amber-700' },
    rose: { bg: 'bg-rose-50', border: 'border-rose-200', text: 'text-rose-700', dot: 'bg-rose-500', badge: 'bg-rose-100 text-rose-700' },
};
</script>

<template>
    <Head title="Jadwal Mengajar" />
    <TeacherLayout>
        <template #header-title>
            <h1 class="text-lg font-bold text-slate-800">Jadwal Mengajar</h1>
        </template>
        <template #header-subtitle>
            <p class="text-xs text-slate-500">Jadwal kegiatan belajar mengajar mingguan Anda</p>
        </template>

        <div class="space-y-6">
            <!-- Day Selector Tabs -->
            <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm p-2">
                <div class="flex gap-1">
                    <button v-for="day in days" :key="day.id" @click="selectedDay = day.id"
                            class="flex-1 py-3 px-2 rounded-lg text-sm font-bold transition-all duration-200"
                            :class="selectedDay === day.id
                                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200'
                                : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700'">
                        <span class="hidden sm:inline">{{ day.name }}</span>
                        <span class="sm:hidden">{{ day.short }}</span>
                    </button>
                </div>
            </div>

            <!-- Summary Bar -->
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm p-4 flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-slate-800">{{ filteredSchedules.length }}</p>
                        <p class="text-xs text-slate-500">Sesi Mengajar</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm p-4 flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-slate-800">{{ totalJam }} Jam</p>
                        <p class="text-xs text-slate-500">Total Durasi</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm p-4 flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-slate-800">{{ filteredSchedules.reduce((a,s) => a + s.students, 0) }}</p>
                        <p class="text-xs text-slate-500">Total Siswa</p>
                    </div>
                </div>
            </div>

            <!-- Schedule Timeline -->
            <div v-if="filteredSchedules.length > 0" class="space-y-3">
                <div v-for="(schedule, idx) in filteredSchedules" :key="schedule.id"
                     class="bg-white rounded-xl border shadow-sm overflow-hidden transition-all hover:shadow-md"
                     :class="colorMap[schedule.color]?.border || 'border-slate-200'">
                    <div class="flex">
                        <!-- Color Stripe -->
                        <div class="w-1.5 shrink-0" :class="colorMap[schedule.color]?.dot || 'bg-slate-400'"></div>
                        <!-- Content -->
                        <div class="flex-1 p-5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex items-start gap-4">
                                    <!-- Time Block -->
                                    <div class="text-center shrink-0 py-2 px-3 rounded-lg bg-slate-50">
                                        <p class="text-lg font-bold text-slate-800">{{ schedule.timeStart }}</p>
                                        <p class="text-xs text-slate-400">{{ schedule.timeEnd }}</p>
                                    </div>
                                    <!-- Info -->
                                    <div>
                                        <h3 class="text-base font-bold text-slate-800">{{ schedule.subject }}</h3>
                                        <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                            <span :class="colorMap[schedule.color]?.badge" class="text-xs font-bold px-2.5 py-0.5 rounded-full">{{ schedule.class }}</span>
                                            <span class="text-xs text-slate-500 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                {{ schedule.room }}
                                            </span>
                                            <span class="text-xs text-slate-500 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                                {{ schedule.students }} siswa
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-2 shrink-0">
                                    <Link :href="route('guru.absensi', { class: schedule.class })" class="px-4 py-2 text-xs font-bold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-sm">
                                        Isi Absensi
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-xl border border-slate-200/60 shadow-sm p-12 text-center">
                <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-700">Tidak Ada Jadwal</h3>
                <p class="text-sm text-slate-500 mt-1">Anda tidak memiliki jadwal mengajar pada hari ini.</p>
            </div>
        </div>
    </TeacherLayout>
</template>
