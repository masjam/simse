<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';
import FormField from '@/Components/ui/FormField.vue';
import Modal from '@/Components/ui/Modal.vue';
import FileInput from '@/Components/ui/FileInput.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    classes: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    lessons: { type: Array, default: () => [] },
});

const confirm = useConfirm();
const toast = useToast();

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

const subjectFilterOptions = computed(() => [
    { value: 'Semua', label: 'Semua Mata Pelajaran' },
    ...subjects.value.map(s => ({ value: s, label: s })),
]);

const classFilterOptions = computed(() => [
    { value: 'Semua', label: 'Semua Kelas' },
    ...classes.value.map(c => ({ value: c, label: `Kelas ${c}` })),
]);

const classFormOptions = computed(() => classes.value.map(c => ({ value: c, label: `Kelas ${c}` })));
const subjectFormOptions = computed(() => subjects.value.map(s => ({ value: s, label: s })));

const showUploadModal = ref(false);
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
        forceFormData: true,
        onSuccess: () => {
            showUploadModal.value = false;
            form.reset();
            toast.success('Materi ajar berhasil diterbitkan.');
        },
    });
};

const deleteLesson = async (id, title) => {
    const agreed = await confirm.ask({
        title: 'Hapus Materi Ajar',
        message: `Yakin ingin menghapus materi "${title}"? Modul dan lampiran tidak dapat dipulihkan.`,
        confirmLabel: 'Ya, Hapus',
        variant: 'danger',
    });
    if (!agreed) return;

    router.delete(route('guru.lms.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Materi ajar berhasil dihapus.');
        },
    });
};
</script>

<template>
    <Head title="Materi Ajar (LMS)" />

    <PortalShell variant="sidebar">
        <template #header>
            <PageHeader
                title="Materi Ajar Digital (LMS)"
                description="Kelola Modul Pembelajaran, Video, dan Bahan Ajar Siswa SD Muhammadiyah Al Mujahidin"
            />
        </template>

        <div class="space-y-4">
            <!-- Filter & Action Bar -->
            <div class="bg-surface rounded-card border border-line p-4 shadow-card flex flex-col sm:flex-row items-end justify-between gap-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full sm:w-auto flex-1">
                    <div>
                        <label for="lms-sub" class="block text-xs font-bold text-ink-muted mb-1">Filter Mata Pelajaran</label>
                        <AppSelect
                            id="lms-sub"
                            v-model="selectedSubject"
                            :options="subjectFilterOptions"
                        />
                    </div>
                    <div>
                        <label for="lms-cls" class="block text-xs font-bold text-ink-muted mb-1">Filter Rombel (Kelas)</label>
                        <AppSelect
                            id="lms-cls"
                            v-model="selectedClass"
                            :options="classFilterOptions"
                        />
                    </div>
                </div>

                <div class="shrink-0 self-start sm:self-auto">
                    <AppButton size="sm" variant="primary" @click="showUploadModal = true">
                        + Tambah Materi Baru
                    </AppButton>
                </div>
            </div>

            <!-- Lessons Grid / Empty State -->
            <div v-if="filteredLessons.length === 0">
                <EmptyState
                    title="Belum Ada Materi"
                    description="Belum ada modul bahan ajar pada filter yang dipilih. Silakan tambah materi baru."
                    action-label="+ Tambah Materi Baru"
                    @action="showUploadModal = true"
                />
            </div>
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                <div
                    v-for="l in filteredLessons"
                    :key="l.id"
                    class="bg-surface rounded-card border border-line p-4 shadow-card hover:bg-surface-raised/40 transition-all flex flex-col justify-between gap-3"
                >
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <span class="px-2 py-0.5 rounded-control bg-brand-50 text-brand-700 text-xs font-bold">
                                {{ l.subject }}
                            </span>
                            <span class="text-xs text-ink-muted">
                                Kelas {{ l.class }} &bull; Urutan #{{ l.order || 1 }}
                            </span>
                        </div>

                        <h4 class="text-sm font-bold text-ink">{{ l.title }}</h4>
                        <p v-if="l.content" class="text-xs text-ink-muted leading-relaxed line-clamp-3 whitespace-pre-line">
                            {{ l.content }}
                        </p>

                        <div v-if="l.video_url" class="p-2 rounded-control bg-surface-raised border border-line text-xs">
                            <span class="text-ink-muted">Tautan Video:</span>
                            <a :href="l.video_url" target="_blank" class="font-bold text-brand-600 hover:underline ml-1 truncate block">
                                {{ l.video_url }}
                            </a>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-line flex items-center justify-between text-xs">
                        <div>
                            <a
                                v-if="l.has_attachment"
                                :href="route('guru.lms.attachment', l.id)"
                                class="font-bold text-brand-600 hover:underline inline-flex items-center gap-1"
                            >
                                📎 Unduh Berkas
                            </a>
                        </div>

                        <AppButton size="sm" variant="danger" @click="deleteLesson(l.id, l.title)">
                            Hapus
                        </AppButton>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Lesson Modal -->
        <Modal :show="showUploadModal" title="Terbitkan Materi Ajar Digital" max-width="lg" @close="showUploadModal = false">
            <form @submit.prevent="submitLesson" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <FormField label="Rombongan Belajar (Kelas)" :error="form.errors.class_name" required>
                        <AppSelect
                            v-model="form.class_name"
                            :options="classFormOptions"
                        />
                    </FormField>

                    <FormField label="Mata Pelajaran" :error="form.errors.subject_name" required>
                        <AppSelect
                            v-model="form.subject_name"
                            :options="subjectFormOptions"
                        />
                    </FormField>
                </div>

                <FormField label="Judul Modul / Materi" :error="form.errors.title" required>
                    <AppInput
                        v-model="form.title"
                        placeholder="Contoh: Bab 4 - Kisah Keteladanan Nabi Muhammad SAW"
                        required
                    />
                </FormField>

                <FormField label="Uraian Pembelajaran" :error="form.errors.content">
                    <AppTextarea
                        v-model="form.content"
                        rows="4"
                        placeholder="Tuliskan petunjuk atau rangkuman materi untuk siswa..."
                    />
                </FormField>

                <FormField label="Tautan Video Pembelajaran (Opsional)" :error="form.errors.video_url">
                    <AppInput
                        v-model="form.video_url"
                        placeholder="https://youtube.com/watch?v=..."
                    />
                </FormField>

                <FormField label="Unggah Berkas Materi / PDF (Opsional)" :error="form.errors.attachment">
                    <FileInput
                        @change="form.attachment = $event"
                    />
                </FormField>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-line">
                    <AppButton type="button" variant="ghost" size="sm" @click="showUploadModal = false">
                        Batal
                    </AppButton>
                    <AppButton type="submit" variant="primary" size="sm" :loading="form.processing">
                        Terbitkan Materi
                    </AppButton>
                </div>
            </form>
        </Modal>
    </PortalShell>
</template>
