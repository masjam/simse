<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    student: { type: Object, required: true },
    subjects: { type: Array, default: () => [] },
    selectedSubjectId: { type: String, default: '' },
    lessons: { type: Array, default: () => [] },
    gamification: { type: Object, default: () => ({ total_xp: 0, total_submissions: 0, level: { level: 1, title: 'Penuntut Ilmu Pemula', icon: '🌱' }, badges: [] }) },
});

const subjectFilter = ref(props.selectedSubjectId || '');
const activeAssignment = ref(null);

const submitForm = useForm({
    text_answer: '',
    file: null,
});

const filterBySubject = () => {
    router.get(route('siswa.lms'), { subject_id: subjectFilter.value }, { preserveState: true, preserveScroll: true });
};

const openSubmitModal = (assignment) => {
    activeAssignment.value = assignment;
    submitForm.reset();
    submitForm.clearErrors();
    submitForm.text_answer = assignment.submission?.text_answer || '';
};

const closeSubmitModal = () => {
    activeAssignment.value = null;
    submitForm.reset();
    submitForm.clearErrors();
};

const handleFileChange = (event) => {
    submitForm.file = event.target.files?.[0] || null;
};

const submitAssignment = () => {
    if (!activeAssignment.value) {
        return;
    }

    submitForm.post(route('siswa.tugas.submit', activeAssignment.value.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            closeSubmitModal();
        },
    });
};
</script>

<template>
    <Head title="Materi & Tugas (LMS)" />

    <StudentLayout>
        <template #header>
            <div>
                <h1 class="text-base sm:text-lg font-extrabold text-slate-800">Materi & Tugas (LMS)</h1>
                <p class="text-xs text-slate-500">Kelas {{ student.class_name }}</p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Gamifikasi Card Siswa -->
            <div class="bg-gradient-to-r from-teal-600 via-emerald-600 to-teal-700 rounded-3xl p-5 sm:p-6 text-white shadow-xl shadow-teal-700/10">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-3xl shadow-inner">
                            {{ gamification.level?.icon || '🌱' }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-400 text-slate-900 font-extrabold text-[10px] tracking-wide uppercase">
                                    Level {{ gamification.level?.level || 1 }}
                                </span>
                                <h2 class="text-base sm:text-lg font-black tracking-tight text-white">
                                    {{ gamification.level?.title || 'Penuntut Ilmu Pemula' }}
                                </h2>
                            </div>
                            <p class="text-xs text-teal-100 mt-1">
                                Total Perolehan: <strong class="text-amber-300 font-bold">{{ gamification.total_xp }} XP</strong> • {{ gamification.total_submissions }} Tugas Terkumpul
                            </p>
                        </div>
                    </div>

                    <!-- Badges Row -->
                    <div class="flex flex-wrap items-center gap-2">
                        <div 
                            v-for="badge in gamification.badges" 
                            :key="badge.name"
                            class="px-3 py-1.5 rounded-xl border flex items-center gap-2 transition-all"
                            :class="badge.unlocked ? 'bg-white/15 border-white/30 text-white' : 'bg-black/20 border-white/10 text-white/40 grayscale'"
                            :title="badge.desc"
                        >
                            <span class="text-base">{{ badge.icon }}</span>
                            <div class="text-left">
                                <p class="text-[11px] font-bold leading-tight">{{ badge.name }}</p>
                                <p class="text-[9px] opacity-80">{{ badge.unlocked ? 'Tercapai' : 'Terkunci' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Mata Pelajaran -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="font-bold text-slate-800 text-sm">Filter Mata Pelajaran</h2>
                    <p class="text-xs text-slate-500">Pilih mata pelajaran untuk menampilkan materi dan tugas terkait.</p>
                </div>
                <select
                    v-model="subjectFilter"
                    class="rounded-xl border-slate-300 text-sm font-semibold text-slate-700 focus:border-teal-500 focus:ring-teal-500"
                    @change="filterBySubject"
                >
                    <option value="">Semua Mata Pelajaran</option>
                    <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                        {{ subject.name }}
                    </option>
                </select>
            </div>

            <!-- Empty State -->
            <div v-if="lessons.length === 0" class="bg-white rounded-2xl border border-slate-200 p-10 text-center text-slate-500 text-sm">
                Belum ada materi pembelajaran yang diterbitkan untuk kelas {{ student.class_name }}.
            </div>

            <!-- Lessons & Assignments -->
            <div v-else class="space-y-5">
                <div
                    v-for="lesson in lessons"
                    :key="lesson.id"
                    class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden"
                >
                    <div class="p-5 border-b border-slate-100 bg-gradient-to-r from-sky-50/80 to-emerald-50/70">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                            <span class="px-3 py-1 rounded-full bg-teal-600 text-white text-xs font-bold">
                                {{ lesson.subject }}
                            </span>
                            <span class="text-xs font-semibold text-slate-500">Bab {{ lesson.order }} &bull; {{ lesson.date }}</span>
                        </div>
                        <h3 class="text-lg font-extrabold text-slate-900">{{ lesson.title }}</h3>
                        <p class="text-xs text-slate-500 mt-1">Guru: {{ lesson.teacher }}</p>
                    </div>

                    <div class="p-5 space-y-4">
                        <p v-if="lesson.content" class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                            {{ lesson.content }}
                        </p>

                        <!-- Attachments & Video -->
                        <div class="flex flex-wrap gap-2">
                            <a
                                v-if="lesson.has_downloadable_file"
                                :href="lesson.attachment_url"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold transition shadow-sm"
                            >
                                <span>Unduh Lampiran Materi</span>
                            </a>
                            <span
                                v-else-if="lesson.attachment"
                                class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold"
                            >
                                Lampiran: {{ lesson.attachment }} (file fisik belum diunggah)
                            </span>

                            <a
                                v-if="lesson.video_url"
                                :href="lesson.video_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold transition"
                            >
                                <span>Tonton Video Pembelajaran</span>
                            </a>
                        </div>

                        <!-- Assignments in Lesson -->
                        <div v-if="lesson.assignments.length > 0" class="pt-3 border-t border-slate-100 space-y-3">
                            <p class="text-xs font-extrabold uppercase tracking-wider text-slate-500">
                                Tugas Bab Ini ({{ lesson.assignments.length }})
                            </p>

                            <div
                                v-for="task in lesson.assignments"
                                :key="task.id"
                                class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                            >
                                <div class="space-y-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-bold text-sm text-slate-800">{{ task.title }}</p>
                                        <span
                                            v-if="task.submission"
                                            class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-bold"
                                        >
                                            Sudah Dikumpulkan
                                        </span>
                                        <span
                                            v-else
                                            class="px-2 py-0.5 rounded-full text-[11px] font-bold"
                                            :class="task.is_overdue ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-800'"
                                        >
                                            {{ task.is_overdue ? 'Melewati Batas' : 'Belum Dikumpulkan' }}
                                        </span>
                                    </div>
                                    <p v-if="task.description" class="text-xs text-slate-600">{{ task.description }}</p>
                                    <p class="text-[11px] text-slate-500">
                                        Batas Waktu: {{ task.due_date }} &bull; Skor Maks: {{ task.max_score }}
                                    </p>

                                    <div v-if="task.submission" class="mt-2 p-3 rounded-lg bg-white border border-slate-200 text-xs space-y-1">
                                        <p class="text-slate-600">
                                            Dikumpulkan: <span class="font-semibold">{{ task.submission.submitted_at }}</span>
                                        </p>
                                        <p v-if="task.submission.score !== null" class="font-bold text-teal-700">
                                            Nilai Guru: {{ task.submission.score }} / {{ task.max_score }}
                                        </p>
                                        <p v-if="task.submission.feedback" class="text-slate-600 italic">
                                            Catatan Guru: "{{ task.submission.feedback }}"
                                        </p>
                                        <a
                                            v-if="task.submission.has_file"
                                            :href="task.submission.file_url"
                                            class="inline-block text-sky-600 font-bold hover:underline"
                                        >
                                            Unduh File Jawaban Saya
                                        </a>
                                    </div>
                                </div>

                                <div class="shrink-0">
                                    <button
                                        type="button"
                                        class="w-full sm:w-auto px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm"
                                        :class="task.submission
                                            ? 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-100'
                                            : 'bg-teal-600 text-white hover:bg-teal-700'"
                                        @click="openSubmitModal(task)"
                                    >
                                        {{ task.submission ? 'Perbarui Jawaban' : 'Kumpulkan Tugas' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Pengumpulan Tugas -->
            <div
                v-if="activeAssignment"
                class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
            >
                <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-900">Kumpulkan Tugas</h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ activeAssignment.title }}</p>
                        </div>
                        <button
                            type="button"
                            class="text-slate-400 hover:text-slate-700 font-bold text-lg"
                            @click="closeSubmitModal"
                        >
                            &times;
                        </button>
                    </div>

                    <form class="space-y-4" @submit.prevent="submitAssignment">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jawaban Teks / Penjelasan</label>
                            <textarea
                                v-model="submitForm.text_answer"
                                rows="4"
                                class="w-full rounded-xl border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500"
                                placeholder="Tuliskan jawaban atau catatan tugas Anda di sini..."
                            ></textarea>
                            <p v-if="submitForm.errors.text_answer" class="text-xs text-rose-600 mt-1">
                                {{ submitForm.errors.text_answer }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Unggah File Tugas (PDF, DOC/DOCX, JPG, PNG, ZIP maks. 10 MB)
                            </label>
                            <input
                                type="file"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip"
                                class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100"
                                @change="handleFileChange"
                            />
                            <p v-if="submitForm.errors.file" class="text-xs text-rose-600 mt-1">
                                {{ submitForm.errors.file }}
                            </p>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button
                                type="button"
                                class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-600 hover:bg-slate-50"
                                @click="closeSubmitModal"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="submitForm.processing"
                                class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow disabled:opacity-60"
                            >
                                {{ submitForm.processing ? 'Mengunggah...' : 'Kirim Tugas' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>

