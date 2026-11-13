<script setup>
import TeacherLayout from '@/Layouts/TeacherLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    classes: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    lessons: { type: Array, default: () => [] },
});

const page = usePage();
const classes = computed(() => props.classes?.length ? props.classes : ['1 Brilliant', 'I OLYMPIAD']);
const subjects = computed(() => props.subjects?.length ? props.subjects : ['Pendidikan Agama Islam dan Budi Pekerti']);

const selectedSubject = ref('Semua');
const selectedClass = ref('Semua');

const filteredLessons = computed(() => {
    return props.lessons.filter(l => {
        const matchSub = selectedSubject.value === 'Semua' || l.subject === selectedSubject.value;
        const matchCls = selectedClass.value === 'Semua' || l.class === selectedClass.value;
        return matchSub && matchCls;
    });
});

const showUploadModal = ref(false);
const attachmentInput = ref(null);
const form = useForm({
    title: '',
    subject_name: subjects.value[0] || 'Pendidikan Agama Islam dan Budi Pekerti',
    class_name: classes.value[0] || '1 Brilliant',
    content: '',
    video_url: '',
    attachment: null,
    order: 1,
});

const submitLesson = () => {
    form.post(route('guru.lms.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showUploadModal.value = false;
            form.reset();
            if (attachmentInput.value) {
                attachmentInput.value.value = '';
            }
        }
    });
};

const deleteLesson = (id) => {
    if (confirm('Yakin ingin menghapus materi ajar ini?')) {
        router.delete(route('guru.lms.destroy', id), {
            preserveScroll: true,
        });
    }
};

const typeIcons = {
    pdf: { icon: 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z', color: 'text-red-500 bg-red-50' },
    video: { icon: 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z', color: 'text-blue-500 bg-blue-50' },
};
</script>

<template>
    <Head title="Materi Ajar (LMS)" />
    <TeacherLayout>
        <template #header-title><h1 class="text-lg font-bold text-slate-800">Materi Ajar Digital (LMS)</h1></template>
        <template #header-subtitle><p class="text-xs text-slate-500">Kelola modul pembelajaran, video dan bahan ajar santri</p></template>

        <div class="space-y-6">
            <!-- Flash Alert -->
            <div v-if="page.props.flash?.success" class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm flex items-center gap-2 shadow-sm">
                <span>✅</span>
                <span class="font-semibold">{{ page.props.flash.success }}</span>
            </div>

            <!-- Filter & Action Bar -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4 flex flex-col md:flex-row gap-3 items-end justify-between">
                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto flex-1">
                    <div class="flex-1">
                        <label class="text-xs font-bold text-slate-600 mb-1 block">Filter Mata Pelajaran</label>
                        <select v-model="selectedSubject" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="Semua">Semua Mata Pelajaran</option>
                            <option v-for="sub in subjects" :key="sub" :value="sub">{{ sub }}</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="text-xs font-bold text-slate-600 mb-1 block">Filter Rombel (Kelas)</label>
                        <select v-model="selectedClass" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="Semua">Semua Rombel</option>
                            <option v-for="cls in classes" :key="cls" :value="cls">{{ cls }}</option>
                        </select>
                    </div>
                </div>
                <button @click="showUploadModal = true" class="px-5 py-2.5 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition shadow-md shadow-emerald-600/20 flex items-center gap-2 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    + Terbitkan Materi Baru
                </button>
            </div>

            <!-- Material List -->
            <div class="space-y-4">
                <div v-for="m in filteredLessons" :key="m.id"
                     class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-5 hover:shadow-md transition">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 border"
                                 :class="typeIcons[m.type]?.color || 'bg-slate-100 text-slate-600'">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="typeIcons[m.type]?.icon"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Bab {{ m.bab }}</span>
                                    <h3 class="font-bold text-slate-800 text-sm md:text-base">{{ m.title }}</h3>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">Aktif</span>
                                </div>
                                <p class="text-xs text-slate-600 leading-relaxed mb-2">{{ m.description }}</p>
                                <div class="flex flex-wrap items-center gap-4 text-xs text-slate-400">
                                    <span>📖 <strong>{{ m.subject }}</strong></span>
                                    <span>🏫 Kelas: <strong>{{ m.class }}</strong></span>
                                    <span>📅 {{ m.date }}</span>
                                    <a v-if="m.attachmentUrl" :href="m.attachmentUrl" class="text-emerald-600 hover:underline font-semibold flex items-center gap-1">
                                        📎 Unduh Lampiran
                                    </a>
                                    <a v-if="m.video_url" :href="m.video_url" target="_blank" class="text-indigo-600 hover:underline font-semibold flex items-center gap-1">
                                        ▶️ Buka Video
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                            <button @click="deleteLesson(m.id)" class="px-3 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-50 border border-rose-200 rounded-lg transition">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="filteredLessons.length === 0" class="bg-white rounded-xl border border-slate-200 p-8 text-center text-slate-400 text-xs">
                    Belum ada materi pembelajaran yang diterbitkan. Silakan klik tombol "+ Terbitkan Materi Baru".
                </div>
            </div>

            <!-- Upload Modal -->
            <Teleport to="body">
                <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
                        <div class="flex justify-between items-center mb-5 pb-3 border-b border-slate-100">
                            <h3 class="font-bold text-slate-800 text-base">Terbitkan Materi Pembelajaran Baru</h3>
                            <button @click="showUploadModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">✕</button>
                        </div>
                        <form @submit.prevent="submitLesson" class="space-y-4">
                            <div>
                                <label class="text-xs font-bold text-slate-700 block mb-1">Judul Materi / Bab</label>
                                <input type="text" v-model="form.title" required placeholder="Contoh: Bab 4 - Kisah Keteladanan Nabi Musa AS"
                                       class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500" />
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-bold text-slate-700 block mb-1">Mata Pelajaran</label>
                                    <select v-model="form.subject_name" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500">
                                        <option v-for="sub in subjects" :key="sub" :value="sub">{{ sub }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-700 block mb-1">Kelas Tujuan</label>
                                    <select v-model="form.class_name" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500">
                                        <option v-for="cls in classes" :key="cls" :value="cls">{{ cls }}</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-700 block mb-1">Ringkasan Materi / Instruksi</label>
                                <textarea v-model="form.content" rows="4" placeholder="Uraikan rangkuman materi pelajaran..."
                                          class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500"></textarea>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-700 block mb-1">Tautan Video Edukasi / YouTube (Opsional)</label>
                                <input type="url" v-model="form.video_url" placeholder="https://www.youtube.com/watch?v=..."
                                       class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500" />
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-700 block mb-1">Lampiran Berkas Materi (Opsional, Maks. 10 MB)</label>
                                <input ref="attachmentInput" type="file" @input="form.attachment = $event.target.files[0] ?? null"
                                       class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" />
                                <p v-if="form.errors.attachment" class="text-[11px] text-rose-600 mt-1">{{ form.errors.attachment }}</p>
                            </div>
                            <div class="flex gap-2.5 pt-3">
                                <button type="button" @click="showUploadModal = false" class="flex-1 py-2 text-xs font-bold text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition">
                                    Batal
                                </button>
                                <button type="submit" :disabled="form.processing" class="flex-1 py-2 text-xs font-bold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition shadow-md shadow-emerald-600/20 disabled:opacity-50">
                                    {{ form.processing ? 'Menyimpan...' : 'Terbitkan Materi' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Teleport>
        </div>
    </TeacherLayout>
</template>
