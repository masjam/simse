<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    student: { type: Object, required: true },
    subjects: { type: Array, default: () => [] },
    weeks: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ subject_id: '', week: '' }) },
    weeklyGroups: { type: Array, default: () => [] },
});

const subjectFilter = ref(props.filters.subject_id || '');
const weekFilter = ref(props.filters.week || '');

const applyFilters = () => {
    router.get(
        route('siswa.materi-mingguan'),
        {
            subject_id: subjectFilter.value,
            week: weekFilter.value,
        },
        { preserveState: true, preserveScroll: true }
    );
};
</script>

<template>
    <Head title="Materi Mingguan Siswa" />

    <StudentLayout>
        <template #header>
            <div>
                <h1 class="text-base sm:text-lg font-extrabold text-slate-800">Materi Mingguan</h1>
                <p class="text-xs text-slate-500">Ringkasan Jurnal Mengajar Guru &bull; Kelas {{ student.class_name }}</p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Filter Bar -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Filter Minggu</label>
                    <select
                        v-model="weekFilter"
                        class="w-full rounded-xl border-slate-300 text-sm font-semibold text-slate-700"
                        @change="applyFilters"
                    >
                        <option value="">Semua Minggu</option>
                        <option v-for="week in weeks" :key="week.key" :value="week.key">
                            {{ week.label }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Filter Mata Pelajaran</label>
                    <select
                        v-model="subjectFilter"
                        class="w-full rounded-xl border-slate-300 text-sm font-semibold text-slate-700"
                        @change="applyFilters"
                    >
                        <option value="">Semua Mata Pelajaran</option>
                        <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                            {{ subject.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="weeklyGroups.length === 0" class="bg-white rounded-2xl border border-slate-200 p-10 text-center text-slate-500 text-sm">
                Belum ada ringkasan materi mingguan dari jurnal guru untuk kelas {{ student.class_name }}.
            </div>

            <!-- Weekly Groups -->
            <div v-else class="space-y-6">
                <div
                    v-for="week in weeklyGroups"
                    :key="week.week_key"
                    class="bg-white rounded-2xl border shadow-sm overflow-hidden"
                    :class="week.is_current_week ? 'border-teal-400 ring-2 ring-teal-100' : 'border-slate-200'"
                >
                    <div
                        class="px-5 py-4 border-b flex flex-wrap items-center justify-between gap-2"
                        :class="week.is_current_week ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white' : 'bg-slate-50 text-slate-800 border-slate-200'"
                    >
                        <div>
                            <h2 class="font-extrabold text-sm sm:text-base">{{ week.week_label }}</h2>
                            <p class="text-xs opacity-85">{{ week.total_entries }} ringkasan pertemuan dari jurnal guru</p>
                        </div>
                        <span
                            v-if="week.is_current_week"
                            class="px-3 py-1 rounded-full bg-white/20 text-xs font-bold"
                        >
                            Minggu Ini
                        </span>
                    </div>

                    <div class="p-5 space-y-5">
                        <div
                            v-for="subject in week.subjects"
                            :key="subject.subject_id"
                            class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 space-y-3"
                        >
                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 pb-2.5">
                                <div>
                                    <h3 class="font-extrabold text-slate-900 text-sm sm:text-base">{{ subject.subject_name }}</h3>
                                    <p class="text-xs text-slate-500">Guru: {{ subject.teacher_name }}</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg bg-sky-100 text-sky-800 text-xs font-bold">
                                    {{ subject.meetings_count }} Pertemuan
                                </span>
                            </div>

                            <div class="space-y-3">
                                <div
                                    v-for="entry in subject.entries"
                                    :key="entry.id"
                                    class="p-3.5 rounded-xl bg-white border border-slate-200 space-y-1.5"
                                >
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="font-bold text-sm text-teal-800">{{ entry.topic }}</p>
                                        <span class="text-[11px] font-semibold text-slate-500">
                                            {{ entry.date }} &bull; Pertemuan #{{ entry.meeting_number }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ entry.summary }}</p>
                                    <p v-if="entry.activities" class="text-xs text-slate-500 pt-1">
                                        <strong class="text-slate-700">Kegiatan:</strong> {{ entry.activities }}
                                    </p>
                                    <p v-if="entry.notes" class="text-xs text-amber-800 bg-amber-50 rounded-lg px-2.5 py-1.5 mt-1">
                                        <strong>Catatan Guru:</strong> {{ entry.notes }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>

