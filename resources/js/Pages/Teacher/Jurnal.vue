<script setup>
import TeacherLayout from '@/Layouts/TeacherLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    journals: { type: Array, default: () => [] },
    classes: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    schedules: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ class_id: '', subject_id: '', week: '' }) },
});

const filterClass = ref(props.filters.class_id || '');
const filterSubject = ref(props.filters.subject_id || '');
const filterWeek = ref(props.filters.week || '');

const showModal = ref(false);
const editingJournal = ref(null);

const form = useForm({
    teaching_schedule_id: '',
    student_class_id: '',
    subject_id: '',
    date: new Date().toISOString().slice(0, 10),
    meeting_number: 1,
    topic: '',
    summary: '',
    activities: '',
    notes: '',
});

const applyFilters = () => {
    router.get(
        route('guru.jurnal'),
        {
            class_id: filterClass.value,
            subject_id: filterSubject.value,
            week: filterWeek.value,
        },
        { preserveState: true, preserveScroll: true }
    );
};

const clearWeekFilter = () => {
    filterWeek.value = '';
    applyFilters();
};

const openCreateModal = () => {
    editingJournal.value = null;
    form.reset();
    form.clearErrors();
    form.date = new Date().toISOString().slice(0, 10);
    form.meeting_number = 1;
    if (props.classes.length) {
        form.student_class_id = props.classes[0].id;
    }
    if (props.subjects.length) {
        form.subject_id = props.subjects[0].id;
    }
    showModal.value = true;
};

const openEditModal = (journal) => {
    editingJournal.value = journal;
    form.clearErrors();
    form.teaching_schedule_id = journal.teaching_schedule_id || '';
    form.student_class_id = journal.student_class_id;
    form.subject_id = journal.subject_id;
    form.date = journal.date;
    form.meeting_number = journal.meeting_number || 1;
    form.topic = journal.topic;
    form.summary = journal.summary;
    form.activities = journal.activities || '';
    form.notes = journal.notes || '';
    showModal.value = true;
};

const onScheduleSelect = () => {
    const selected = props.schedules.find((s) => s.id === form.teaching_schedule_id);
    if (selected) {
        form.student_class_id = selected.student_class_id;
        form.subject_id = selected.subject_id;
    }
};

const submitJournal = () => {
    if (editingJournal.value) {
        form.put(route('guru.jurnal.update', editingJournal.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
            },
        });
        return;
    }

    form.post(route('guru.jurnal.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false;
        },
    });
};

const deleteJournal = (journal) => {
    if (!confirm(`Hapus jurnal topik "${journal.topic}"?`)) {
        return;
    }
    router.delete(route('guru.jurnal.destroy', journal.id), { preserveScroll: true });
};

const weeklyClassSummaryCount = computed(() => {
    const keys = new Set(props.journals.map((j) => `${j.class_name}-${j.subject_name}`));
    return keys.size;
});
</script>

<template>
    <Head title="Jurnal Mengajar Guru" />

    <TeacherLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-xl font-black text-slate-900">Jurnal Mengajar & Ringkasan Materi</h2>
                    <p class="text-xs text-slate-500">
                        Setiap ringkasan jurnal pembelajaran otomatis dirangkum sebagai Materi Mingguan di Portal Siswa.
                    </p>
                </div>
                <button
                    type="button"
                    class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold shadow"
                    @click="openCreateModal"
                >
                    + Tulis Jurnal Mengajar
                </button>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Info & Filter -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Kelas</label>
                        <select
                            v-model="filterClass"
                            class="w-full rounded-xl border-slate-300 text-sm"
                            @change="applyFilters"
                        >
                            <option value="">Semua Kelas</option>
                            <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                {{ cls.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Mata Pelajaran</label>
                        <select
                            v-model="filterSubject"
                            class="w-full rounded-xl border-slate-300 text-sm"
                            @change="applyFilters"
                        >
                            <option value="">Semua Mata Pelajaran</option>
                            <option v-for="subj in subjects" :key="subj.id" :value="subj.id">
                                {{ subj.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Minggu</label>
                        <div class="flex gap-2">
                            <input
                                v-model="filterWeek"
                                type="week"
                                class="w-full rounded-xl border-slate-300 text-sm"
                                @change="applyFilters"
                            />
                            <button
                                v-if="filterWeek"
                                type="button"
                                class="px-3 py-2 rounded-xl bg-slate-100 text-xs font-bold text-slate-600 hover:bg-slate-200"
                                @click="clearWeekFilter"
                            >
                                Semua
                            </button>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-indigo-50 border border-indigo-100 flex flex-col justify-center">
                        <p class="text-[11px] font-bold text-indigo-700 uppercase">Total Catatan Tampil</p>
                        <p class="text-lg font-black text-indigo-950">
                            {{ journals.length }} Jurnal ({{ weeklyClassSummaryCount }} Mapel/Kelas)
                        </p>
                    </div>
                </div>
            </div>

            <!-- Journal List -->
            <div v-if="journals.length === 0" class="bg-white rounded-2xl border border-slate-200 p-10 text-center text-sm text-slate-500">
                Belum ada jurnal mengajar pada filter minggu ini. Klik <strong>+ Tulis Jurnal Mengajar</strong> untuk menambahkan.
            </div>

            <div v-else class="space-y-4">
                <div
                    v-for="journal in journals"
                    :key="journal.id"
                    class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-3"
                >
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                        <div>
                            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                <span class="px-2.5 py-1 rounded-lg bg-indigo-100 text-indigo-800 text-xs font-bold">
                                    Kelas {{ journal.class_name }}
                                </span>
                                <span class="px-2.5 py-1 rounded-lg bg-sky-100 text-sky-800 text-xs font-bold">
                                    {{ journal.subject_name }}
                                </span>
                                <span class="text-xs font-semibold text-slate-500">
                                    {{ journal.date_formatted }} &bull; Pertemuan #{{ journal.meeting_number }}
                                </span>
                            </div>
                            <h3 class="text-base font-extrabold text-slate-900">{{ journal.topic }}</h3>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <button
                                type="button"
                                class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50"
                                @click="openEditModal(journal)"
                            >
                                Ubah
                            </button>
                            <button
                                type="button"
                                class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold hover:bg-rose-100"
                                @click="deleteJournal(journal)"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 space-y-1.5">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            Ringkasan Jurnal (Tampil di Materi Mingguan Siswa)
                        </p>
                        <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ journal.summary }}</p>
                    </div>

                    <div v-if="journal.activities || journal.notes" class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                        <div v-if="journal.activities" class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-100">
                            <p class="font-bold text-emerald-800 mb-0.5">Kegiatan Pembelajaran</p>
                            <p class="text-slate-700">{{ journal.activities }}</p>
                        </div>
                        <div v-if="journal.notes" class="p-3 rounded-xl bg-amber-50/70 border border-amber-100">
                            <p class="font-bold text-amber-800 mb-0.5">Catatan / Tindak Lanjut</p>
                            <p class="text-slate-700">{{ journal.notes }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Form -->
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
            >
                <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl max-h-[90vh] overflow-y-auto space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-lg font-extrabold text-slate-900">
                            {{ editingJournal ? 'Perbarui Jurnal Mengajar' : 'Tambah Jurnal Mengajar' }}
                        </h3>
                        <button type="button" class="text-slate-400 hover:text-slate-700 text-xl font-bold" @click="showModal = false">
                            &times;
                        </button>
                    </div>

                    <form class="space-y-4" @submit.prevent="submitJournal">
                        <div v-if="schedules.length">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Ambil Cepat dari Jadwal Mengajar (Opsional)</label>
                            <select
                                v-model="form.teaching_schedule_id"
                                class="w-full rounded-xl border-slate-300 text-sm"
                                @change="onScheduleSelect"
                            >
                                <option value="">-- Pilih Manual Kelas & Mapel --</option>
                                <option v-for="sched in schedules" :key="sched.id" :value="sched.id">
                                    {{ sched.label }}
                                </option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kelas</label>
                                <select v-model="form.student_class_id" class="w-full rounded-xl border-slate-300 text-sm">
                                    <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                                </select>
                                <p v-if="form.errors.student_class_id" class="text-xs text-rose-600 mt-1">
                                    {{ form.errors.student_class_id }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Mata Pelajaran</label>
                                <select v-model="form.subject_id" class="w-full rounded-xl border-slate-300 text-sm">
                                    <option v-for="subj in subjects" :key="subj.id" :value="subj.id">{{ subj.name }}</option>
                                </select>
                                <p v-if="form.errors.subject_id" class="text-xs text-rose-600 mt-1">
                                    {{ form.errors.subject_id }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mengajar</label>
                                <input v-model="form.date" type="date" class="w-full rounded-xl border-slate-300 text-sm" />
                                <p v-if="form.errors.date" class="text-xs text-rose-600 mt-1">{{ form.errors.date }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Pertemuan Ke-</label>
                                <input v-model="form.meeting_number" type="number" min="1" class="w-full rounded-xl border-slate-300 text-sm" />
                                <p v-if="form.errors.meeting_number" class="text-xs text-rose-600 mt-1">{{ form.errors.meeting_number }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Topik / Pokok Bahasan</label>
                            <input
                                v-model="form.topic"
                                type="text"
                                class="w-full rounded-xl border-slate-300 text-sm"
                                placeholder="Contoh: Perkalian Bilangan Cacah"
                            />
                            <p v-if="form.errors.topic" class="text-xs text-rose-600 mt-1">{{ form.errors.topic }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Ringkasan Materi Jurnal (Ditampilkan ke Siswa pada Materi Mingguan)
                            </label>
                            <textarea
                                v-model="form.summary"
                                rows="4"
                                class="w-full rounded-xl border-slate-300 text-sm"
                                placeholder="Tuliskan inti materi pembelajaran hari ini yang perlu dipelajari ulang oleh siswa..."
                            ></textarea>
                            <p v-if="form.errors.summary" class="text-xs text-rose-600 mt-1">{{ form.errors.summary }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kegiatan Pembelajaran (Opsional)</label>
                                <textarea
                                    v-model="form.activities"
                                    rows="2"
                                    class="w-full rounded-xl border-slate-300 text-sm"
                                    placeholder="Diskusi kelompok, latihan soal..."
                                ></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan / Tindak Lanjut (Opsional)</label>
                                <textarea
                                    v-model="form.notes"
                                    rows="2"
                                    class="w-full rounded-xl border-slate-300 text-sm"
                                    placeholder="Pelajari halaman 24 untuk pertemuan berikutnya..."
                                ></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button
                                type="button"
                                class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-600 hover:bg-slate-50"
                                @click="showModal = false"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow disabled:opacity-60"
                            >
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Jurnal' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </TeacherLayout>
</template>

