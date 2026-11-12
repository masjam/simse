<script setup>
import TeacherLayout from '@/Layouts/TeacherLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    classes: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    exams: { type: Array, default: () => [] },
});

const page = usePage();
const activeTab = ref('exams');

const statusColors = {
    scheduled: 'bg-blue-100 text-blue-700 border-blue-200',
    draft: 'bg-amber-100 text-amber-700 border-amber-200',
    active: 'bg-emerald-100 text-emerald-700 border-emerald-200',
    completed: 'bg-slate-100 text-slate-600 border-slate-200',
};
const statusLabels = { scheduled: 'Terjadwal', draft: 'Draft', active: 'Sedang Berlangsung', completed: 'Selesai' };

// Create Exam Modal
const showCreateExam = ref(false);
const examForm = useForm({
    title: '',
    subject_name: props.subjects[0] || 'Pendidikan Agama Islam dan Budi Pekerti',
    start_time: new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString().slice(0, 16),
    end_time: new Date(Date.now() + 26 * 60 * 60 * 1000).toISOString().slice(0, 16),
    duration_minutes: 60,
    max_violations: 3,
    description: '',
});

const submitExam = () => {
    examForm.post(route('guru.cbt.exam.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateExam.value = false;
            examForm.reset();
        }
    });
};

const toggleStatus = (examId) => {
    router.patch(route('guru.cbt.exam.toggle', examId), {}, {
        preserveScroll: true,
    });
};

// Add Question Modal
const showAddQuestion = ref(false);
const activeExam = ref(null);
const questionForm = useForm({
    type: 'Pilihan Ganda',
    content: '',
    marks: 10,
    options: [
        { content: '', is_correct: true },
        { content: '', is_correct: false },
        { content: '', is_correct: false },
        { content: '', is_correct: false },
    ],
});

const openAddQuestion = (exam) => {
    activeExam.value = exam;
    showAddQuestion.value = true;
};

const submitQuestion = () => {
    if (!activeExam.value) return;
    questionForm.post(route('guru.cbt.question.store', activeExam.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showAddQuestion.value = false;
            questionForm.reset();
        }
    });
};
</script>

<template>
    <Head title="Bank Soal & CBT" />
    <TeacherLayout>
        <template #header-title><h1 class="text-lg font-bold text-slate-800">Bank Soal & Ujian CBT</h1></template>
        <template #header-subtitle><p class="text-xs text-slate-500">Computer Based Test dengan Anti-Cheat Engine SD Muhammadiyah Al Mujahidin</p></template>

        <div class="space-y-6">
            <!-- Flash Message -->
            <div v-if="page.props.flash?.success" class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm flex items-center gap-2 shadow-sm">
                <span>✅</span>
                <span class="font-semibold">{{ page.props.flash.success }}</span>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4">
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <p class="text-2xl font-black text-slate-800">{{ exams.length }}</p>
                    <p class="text-xs text-slate-500">Jadwal Ujian CBT</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4">
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <p class="text-2xl font-black text-slate-800">{{ exams.reduce((a,b) => a + (b.totalQuestions || 0), 0) }}</p>
                    <p class="text-xs text-slate-500">Total Butir Soal</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4">
                    <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <p class="text-2xl font-black text-slate-800">{{ exams.reduce((a,b) => a + (b.participants || 0), 0) }}</p>
                    <p class="text-xs text-slate-500">Siswa Telah Ujian</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4">
                    <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <p class="text-2xl font-black text-slate-800">Aktif</p>
                    <p class="text-xs text-slate-500">Proteksi Anti-Cheat</p>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4 flex justify-between items-center">
                <h3 class="font-bold text-slate-800 text-sm">Daftar Paket Ujian Online</h3>
                <button @click="showCreateExam = true" class="px-5 py-2.5 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition shadow-md shadow-emerald-600/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    + Buat Ujian CBT Baru
                </button>
            </div>

            <!-- Exam Cards -->
            <div class="space-y-4">
                <div v-for="e in exams" :key="e.id"
                     class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 hover:shadow-md transition">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded border border-emerald-200">{{ e.subject }}</span>
                                <span class="text-xs font-bold px-2.5 py-0.5 rounded border" :class="statusColors[e.status]">{{ statusLabels[e.status] }}</span>
                                <span class="text-[11px] text-slate-400 font-semibold">Toleransi Switch: Max {{ e.maxViolations }}x</span>
                            </div>
                            <h3 class="text-lg font-bold text-slate-800">{{ e.title }}</h3>
                            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500">
                                <span>📅 {{ e.date }} ({{ e.time }})</span>
                                <span>⏳ Durasi: <strong>{{ e.duration }} Menit</strong></span>
                                <span>📝 Butir Soal: <strong>{{ e.totalQuestions }} Soal</strong></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2.5 self-end md:self-center">
                            <button @click="openAddQuestion(e)" class="px-3.5 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold rounded-lg hover:bg-emerald-100 transition">
                                + Tambah Soal
                            </button>
                            <button @click="toggleStatus(e.id)" class="px-3.5 py-2 border border-slate-200 text-slate-600 text-xs font-bold rounded-lg hover:bg-slate-50 transition">
                                {{ e.isActive ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </div>
                    </div>

                    <!-- Questions Preview inside Exam -->
                    <div v-if="e.questions && e.questions.length > 0" class="mt-5 pt-4 border-t border-slate-100">
                        <p class="text-xs font-bold text-slate-600 mb-2">Pratinjau Butir Soal ({{ e.questions.length }}):</p>
                        <div class="space-y-2">
                            <div v-for="(q, qIdx) in e.questions" :key="q.id" class="p-3 bg-slate-50 rounded-lg text-xs space-y-1">
                                <div class="flex justify-between items-start font-semibold text-slate-800">
                                    <span>{{ qIdx + 1 }}. {{ q.content }}</span>
                                    <span class="text-[10px] text-slate-400 border border-slate-200 px-1.5 py-0.5 rounded bg-white">{{ q.type }} ({{ q.marks }} Poin)</span>
                                </div>
                                <div v-if="q.options && q.options.length > 0" class="grid grid-cols-2 gap-1.5 pt-1">
                                    <div v-for="(opt, oIdx) in q.options" :key="oIdx" 
                                         :class="opt.is_correct ? 'text-emerald-700 font-bold bg-emerald-100/50' : 'text-slate-500'"
                                         class="px-2 py-0.5 rounded text-[11px]">
                                        • {{ opt.content }} {{ opt.is_correct ? '✓ (Kunci)' : '' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="exams.length === 0" class="bg-white rounded-xl border border-slate-200 p-8 text-center text-slate-400 text-xs">
                    Belum ada ujian CBT yang dijadwalkan. Klik tombol "+ Buat Ujian CBT Baru".
                </div>
            </div>

            <!-- Create Exam Modal -->
            <Teleport to="body">
                <div v-if="showCreateExam" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
                        <div class="flex justify-between items-center mb-5 pb-3 border-b border-slate-100">
                            <h3 class="font-bold text-slate-800 text-base">Jadwalkan Ujian Online CBT Baru</h3>
                            <button @click="showCreateExam = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm">✕</button>
                        </div>
                        <form @submit.prevent="submitExam" class="space-y-4">
                            <div>
                                <label class="text-xs font-bold text-slate-700 block mb-1">Nama Ujian</label>
                                <input type="text" v-model="examForm.title" required placeholder="Contoh: Penilaian Harian Bab 2 PAI"
                                       class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500" />
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-700 block mb-1">Mata Pelajaran</label>
                                <select v-model="examForm.subject_name" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500">
                                    <option v-for="sub in subjects" :key="sub" :value="sub">{{ sub }}</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-bold text-slate-700 block mb-1">Waktu Mulai</label>
                                    <input type="datetime-local" v-model="examForm.start_time" required
                                           class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500" />
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-700 block mb-1">Waktu Berakhir</label>
                                    <input type="datetime-local" v-model="examForm.end_time" required
                                           class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500" />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-bold text-slate-700 block mb-1">Durasi Pengerjaan (Menit)</label>
                                    <input type="number" v-model="examForm.duration_minutes" min="10" required
                                           class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500" />
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-700 block mb-1">Batas Toleransi Tab Switch</label>
                                    <input type="number" v-model="examForm.max_violations" min="1" max="10" required
                                           class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500" />
                                </div>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-700 block mb-1">Petunjuk Ujian</label>
                                <textarea v-model="examForm.description" rows="2" placeholder="Petunjuk tata tertib..."
                                          class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500"></textarea>
                            </div>
                            <div class="flex gap-2.5 pt-3">
                                <button type="button" @click="showCreateExam = false" class="flex-1 py-2 text-xs font-bold text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition">
                                    Batal
                                </button>
                                <button type="submit" :disabled="examForm.processing" class="flex-1 py-2 text-xs font-bold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition shadow-md shadow-emerald-600/20 disabled:opacity-50">
                                    {{ examForm.processing ? 'Menjadwalkan...' : 'Jadwalkan Ujian' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Teleport>

            <!-- Add Question Modal -->
            <Teleport to="body">
                <div v-if="showAddQuestion && activeExam" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
                        <div class="flex justify-between items-center mb-5 pb-3 border-b border-slate-100">
                            <div>
                                <h3 class="font-bold text-slate-800 text-base">Tambah Butir Soal Baru</h3>
                                <p class="text-xs text-slate-400">{{ activeExam.title }}</p>
                            </div>
                            <button @click="showAddQuestion = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm">✕</button>
                        </div>
                        <form @submit.prevent="submitQuestion" class="space-y-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-bold text-slate-700 block mb-1">Tipe Soal</label>
                                    <select v-model="questionForm.type" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500">
                                        <option>Pilihan Ganda</option>
                                        <option>Benar Salah</option>
                                        <option>Esai</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-700 block mb-1">Bobot Nilai</label>
                                    <input type="number" v-model="questionForm.marks" min="1" required
                                           class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500" />
                                </div>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-700 block mb-1">Pertanyaan / Soal</label>
                                <textarea v-model="questionForm.content" required rows="3" placeholder="Tuliskan teks pertanyaan soal..."
                                          class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500"></textarea>
                            </div>
                            <div v-if="questionForm.type === 'Pilihan Ganda'" class="space-y-2">
                                <label class="text-xs font-bold text-slate-700 block">Pilihan Jawaban (Tandai yang benar)</label>
                                <div v-for="(opt, idx) in questionForm.options" :key="idx" class="flex items-center gap-2">
                                    <input type="radio" :name="'correct_opt'" :checked="opt.is_correct" @change="questionForm.options.forEach((o, i) => o.is_correct = (i === idx))" />
                                    <input type="text" v-model="opt.content" :placeholder="'Pilihan ' + String.fromCharCode(65 + idx)"
                                           class="w-full text-xs px-2.5 py-1.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500" />
                                </div>
                            </div>
                            <div class="flex gap-2.5 pt-3">
                                <button type="button" @click="showAddQuestion = false" class="flex-1 py-2 text-xs font-bold text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition">
                                    Batal
                                </button>
                                <button type="submit" :disabled="questionForm.processing" class="flex-1 py-2 text-xs font-bold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition shadow-md shadow-emerald-600/20 disabled:opacity-50">
                                    {{ questionForm.processing ? 'Menyimpan...' : 'Simpan Soal' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Teleport>
        </div>
    </TeacherLayout>
</template>
