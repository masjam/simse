<script setup>
import TeacherLayout from '@/Layouts/TeacherLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    classes: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    lessons: { type: Array, default: () => [] },
    assignments: { type: Array, default: () => [] },
});

const page = usePage();
const activeTab = ref('active');
const filteredAssignments = computed(() => {
    return props.assignments.filter(a => activeTab.value === 'all' || a.status === activeTab.value);
});

// Create Assignment Modal
const showCreateModal = ref(false);
const createForm = useForm({
    title: '',
    lesson_id: props.lessons[0]?.id || '',
    description: '',
    due_date: new Date(Date.now() + 3 * 24 * 60 * 60 * 1000).toISOString().slice(0, 16),
    max_score: 100,
});

const submitAssignment = () => {
    createForm.post(route('guru.tugas.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        }
    });
};

// Submissions Review Modal
const showReviewModal = ref(false);
const activeAssignment = ref(null);
const gradingForm = useForm({
    score: '',
    feedback: '',
});
const activeSubmissionId = ref(null);

const openReview = (assignment) => {
    activeAssignment.value = assignment;
    showReviewModal.value = true;
};

const selectSubmission = (sub) => {
    activeSubmissionId.value = sub.id;
    gradingForm.score = sub.score !== null ? sub.score : '';
    gradingForm.feedback = sub.feedback || '';
};

const submitGrade = (submissionId) => {
    gradingForm.post(route('guru.tugas.grade', submissionId), {
        preserveScroll: true,
        onSuccess: () => {
            activeSubmissionId.value = null;
            gradingForm.reset();
        }
    });
};
</script>

<template>
    <Head title="Tugas Siswa" />
    <TeacherLayout>
        <template #header-title><h1 class="text-lg font-bold text-slate-800">Tugas & Pengumpulan Santri</h1></template>
        <template #header-subtitle><p class="text-xs text-slate-500">Kelola tugas harian dan berikan penilaian serta catatan pembinaan</p></template>

        <div class="space-y-6">
            <!-- Flash Message -->
            <div v-if="page.props.flash?.success" class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm flex items-center gap-2 shadow-sm">
                <span>✅</span>
                <span class="font-semibold">{{ page.props.flash.success }}</span>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4">
                    <p class="text-2xl font-black text-slate-800">{{ assignments.filter(a => a.status === 'active').length }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Tugas Aktif</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4">
                    <p class="text-2xl font-black text-amber-600">{{ assignments.reduce((acc, t) => acc + (t.submitted - t.graded), 0) }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Belum Dikoreksi</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4">
                    <p class="text-2xl font-black text-emerald-600">{{ assignments.reduce((acc, t) => acc + t.graded, 0) }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Sudah Dinilai</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4">
                    <p class="text-2xl font-black text-slate-800">{{ assignments.length }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Total Tugas Diberikan</p>
                </div>
            </div>

            <!-- Tabs + Actions -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4 flex flex-col sm:flex-row justify-between items-center gap-3">
                <div class="flex gap-1 bg-slate-100 p-1 rounded-lg">
                    <button @click="activeTab = 'active'" class="px-4 py-2 text-xs font-bold rounded-md transition"
                            :class="activeTab === 'active' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500'">Aktif</button>
                    <button @click="activeTab = 'completed'" class="px-4 py-2 text-xs font-bold rounded-md transition"
                            :class="activeTab === 'completed' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500'">Selesai</button>
                    <button @click="activeTab = 'all'" class="px-4 py-2 text-xs font-bold rounded-md transition"
                            :class="activeTab === 'all' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500'">Semua</button>
                </div>
                <button @click="showCreateModal = true" class="px-5 py-2.5 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition shadow-md shadow-emerald-600/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    + Buat Tugas Baru
                </button>
            </div>

            <!-- Assignment Cards -->
            <div class="space-y-4">
                <div v-for="a in filteredAssignments" :key="a.id"
                     class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-5 hover:shadow-md transition">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-md border border-emerald-200">
                                    {{ a.subject }}
                                </span>
                                <span class="text-[11px] font-semibold text-slate-500">Kelas: {{ a.class }}</span>
                                <span :class="a.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'" class="text-[10px] font-black px-2 py-0.5 rounded uppercase">
                                    {{ a.status === 'active' ? 'Sedang Berlangsung' : 'Selesai' }}
                                </span>
                            </div>
                            <h3 class="text-base font-bold text-slate-800">{{ a.title }}</h3>
                            <p class="text-xs text-slate-400">📅 Batas Pengumpulan: <strong class="text-slate-600">{{ a.deadline }}</strong></p>
                        </div>

                        <!-- Progress & Actions -->
                        <div class="flex items-center gap-6">
                            <div class="text-right">
                                <p class="text-xs font-semibold text-slate-500 mb-0.5">Pengumpulan: <strong>{{ a.submitted }} / {{ a.totalStudents }}</strong></p>
                                <div class="w-32 bg-slate-100 rounded-full h-2">
                                    <div class="bg-emerald-500 h-2 rounded-full" :style="`width: ${Math.round((a.submitted / (a.totalStudents || 1)) * 100)}%`"></div>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-1">Sudah dinilai: {{ a.graded }} siswa</p>
                            </div>
                            <button @click="openReview(a)" class="px-4 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold rounded-lg hover:bg-emerald-100 transition whitespace-nowrap">
                                Koreksi & Nilai ({{ a.submitted }})
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="filteredAssignments.length === 0" class="bg-white rounded-xl border border-slate-200 p-8 text-center text-slate-400 text-xs">
                    Tidak ada tugas yang ditemukan pada kategori ini.
                </div>
            </div>

            <!-- Create Assignment Modal -->
            <Teleport to="body">
                <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
                        <div class="flex justify-between items-center mb-5 pb-3 border-b border-slate-100">
                            <h3 class="font-bold text-slate-800 text-base">Buat Tugas Baru untuk Santri</h3>
                            <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">✕</button>
                        </div>
                        <form @submit.prevent="submitAssignment" class="space-y-4">
                            <div>
                                <label class="text-xs font-bold text-slate-700 block mb-1">Judul Tugas</label>
                                <input type="text" v-model="createForm.title" required placeholder="Contoh: Hafalan Surat Al-Ma'un dan Terjemahan"
                                       class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500" />
                            </div>
                            <div v-if="lessons.length > 0">
                                <label class="text-xs font-bold text-slate-700 block mb-1">Kaitkan ke Materi Bab</label>
                                <select v-model="createForm.lesson_id" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500">
                                    <option v-for="l in lessons" :key="l.id" :value="l.id">{{ l.title }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-700 block mb-1">Instruksi Pengerjaan Tugas</label>
                                <textarea v-model="createForm.description" rows="3" placeholder="Tuliskan petunjuk pengerjaan bagi siswa..."
                                          class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500"></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-bold text-slate-700 block mb-1">Tenggat Waktu (Deadline)</label>
                                    <input type="datetime-local" v-model="createForm.due_date" required
                                           class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500" />
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-700 block mb-1">Nilai Maksimal</label>
                                    <input type="number" v-model="createForm.max_score" min="1" max="100" required
                                           class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500" />
                                </div>
                            </div>
                            <div class="flex gap-2.5 pt-3">
                                <button type="button" @click="showCreateModal = false" class="flex-1 py-2 text-xs font-bold text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition">
                                    Batal
                                </button>
                                <button type="submit" :disabled="createForm.processing" class="flex-1 py-2 text-xs font-bold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition shadow-md shadow-emerald-600/20 disabled:opacity-50">
                                    {{ createForm.processing ? 'Menyimpan...' : 'Bagikan Tugas' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Teleport>

            <!-- Review / Grading Modal -->
            <Teleport to="body">
                <div v-if="showReviewModal && activeAssignment" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl p-6 md:p-8 max-h-[90vh] overflow-y-auto">
                        <div class="flex justify-between items-start mb-6 pb-4 border-b border-slate-100">
                            <div>
                                <h3 class="font-bold text-slate-800 text-lg">Pemeriksaan Tugas: {{ activeAssignment.title }}</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Rombel: {{ activeAssignment.class }} — Mapel: {{ activeAssignment.subject }}</p>
                            </div>
                            <button @click="showReviewModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm">✕</button>
                        </div>

                        <!-- Submissions List -->
                        <div class="space-y-4">
                            <div v-for="sub in activeAssignment.submissions" :key="sub.id"
                                 class="border border-slate-200 rounded-xl p-4 bg-slate-50/50 space-y-3">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-bold text-slate-800 text-sm">{{ sub.studentName }}</h4>
                                        <p class="text-[11px] text-slate-400">NISN: {{ sub.nisn }} • Dikumpulkan: {{ sub.submittedAt }}</p>
                                    </div>
                                    <span v-if="sub.score !== null" class="px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-black rounded-lg">
                                        Nilai: {{ sub.score }} / 100
                                    </span>
                                    <span v-else class="px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-lg">
                                        Belum Dinilai
                                    </span>
                                </div>

                                <div v-if="sub.textAnswer" class="bg-white p-3 rounded-lg border border-slate-200 text-xs text-slate-700">
                                    <p class="font-bold text-slate-500 mb-1">Jawaban Teks Santri:</p>
                                    {{ sub.textAnswer }}
                                </div>

                                <div v-if="sub.feedback" class="text-xs text-emerald-700 bg-emerald-50/80 p-2.5 rounded-lg border border-emerald-100">
                                    <strong>Catatan Guru:</strong> {{ sub.feedback }}
                                </div>

                                <!-- Grading Form Toggle -->
                                <div v-if="activeSubmissionId === sub.id" class="p-3 bg-white border border-emerald-200 rounded-lg space-y-3 mt-2">
                                    <div class="grid grid-cols-3 gap-3">
                                        <div>
                                            <label class="text-[11px] font-bold text-slate-700 block mb-1">Skor Nilai (0-100)</label>
                                            <input type="number" v-model="gradingForm.score" min="0" max="100" required
                                                   class="w-full text-xs px-3 py-1.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500" />
                                        </div>
                                        <div class="col-span-2">
                                            <label class="text-[11px] font-bold text-slate-700 block mb-1">Catatan Pembinaan / Feedback</label>
                                            <input type="text" v-model="gradingForm.feedback" placeholder="Contoh: Luar biasa, sangat rapi dan teliti!"
                                                   class="w-full text-xs px-3 py-1.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500" />
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-2">
                                        <button @click="activeSubmissionId = null" class="px-3 py-1 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-lg">Batal</button>
                                        <button @click="submitGrade(sub.id)" :disabled="gradingForm.processing" class="px-4 py-1.5 bg-emerald-600 text-white font-bold text-xs rounded-lg hover:bg-emerald-700 transition">
                                            {{ gradingForm.processing ? 'Menyimpan...' : 'Simpan Nilai' }}
                                        </button>
                                    </div>
                                </div>
                                <div v-else class="flex justify-end">
                                    <button @click="selectSubmission(sub)" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                                        {{ sub.score !== null ? '✏️ Ubah Nilai' : '📝 Beri Nilai' }}
                                    </button>
                                </div>
                            </div>

                            <div v-if="activeAssignment.submissions.length === 0" class="text-center py-6 text-slate-400 text-xs">
                                Belum ada santri yang mengumpulkan tugas ini.
                            </div>
                        </div>
                    </div>
                </div>
            </Teleport>
        </div>
    </TeacherLayout>
</template>
