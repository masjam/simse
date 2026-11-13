<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';
import AppButton from '@/Components/ui/AppButton.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    journal: { type: Object, default: null },
    classes: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    schedules: { type: Array, default: () => [] },
});

const emit = defineEmits(['close']);

const form = useForm({
    teaching_schedule_id: props.journal?.teaching_schedule_id || '',
    student_class_id: props.journal?.student_class_id || (props.classes[0]?.id || ''),
    subject_id: props.journal?.subject_id || (props.subjects[0]?.id || ''),
    date: props.journal?.date || new Date().toISOString().slice(0, 10),
    meeting_number: props.journal?.meeting_number || 1,
    topic: props.journal?.topic || '',
    summary: props.journal?.summary || '',
    activities: props.journal?.activities || '',
    notes: props.journal?.notes || '',
});

const classOptions = computed(() => props.classes.map(c => ({ value: c.id, label: c.name })));
const subjectOptions = computed(() => props.subjects.map(s => ({ value: s.id, label: s.name })));

const submit = () => {
    if (props.journal) {
        form.put(route('guru.jurnal.update', props.journal.id), {
            preserveScroll: true,
            onSuccess: () => emit('close'),
        });
    } else {
        form.post(route('guru.jurnal.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                emit('close');
            },
        });
    }
};
</script>

<template>
    <Modal
        :show="show"
        :title="journal ? 'Edit Catatan Jurnal Mengajar' : 'Tambah Jurnal Mengajar Baru'"
        max-width="lg"
        @close="emit('close')"
    >
        <form @submit.prevent="submit" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <FormField label="Kelas / Rombel" :error="form.errors.student_class_id" required>
                    <AppSelect
                        v-model="form.student_class_id"
                        :options="classOptions"
                    />
                </FormField>

                <FormField label="Mata Pelajaran" :error="form.errors.subject_id" required>
                    <AppSelect
                        v-model="form.subject_id"
                        :options="subjectOptions"
                    />
                </FormField>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <FormField label="Tanggal Pertemuan" :error="form.errors.date" required>
                    <AppInput
                        v-model="form.date"
                        type="date"
                        required
                    />
                </FormField>

                <FormField label="Pertemuan Ke-" :error="form.errors.meeting_number" required>
                    <AppInput
                        v-model="form.meeting_number"
                        type="number"
                        min="1"
                        required
                    />
                </FormField>
            </div>

            <FormField label="Materi / Topik Pembelajaran" :error="form.errors.topic" required>
                <AppInput
                    v-model="form.topic"
                    placeholder="Contoh: Bab 3 - Adab Makan dan Minum Sesuai Sunnah"
                    required
                />
            </FormField>

            <FormField label="Ringkasan Materi (Ditampilkan ke Siswa & Ortu)" :error="form.errors.summary" required>
                <AppTextarea
                    v-model="form.summary"
                    rows="3"
                    placeholder="Tuliskan pokok bahasan ringkas yang dipelajari siswa..."
                    required
                />
            </FormField>

            <FormField label="Aktivitas Kelas & Penugasan" :error="form.errors.activities">
                <AppTextarea
                    v-model="form.activities"
                    rows="2"
                    placeholder="Catatan aktivitas diskusi, hafalan, atau latihan..."
                />
            </FormField>

            <FormField label="Catatan Refleksi Guru (Internal)" :error="form.errors.notes">
                <AppInput
                    v-model="form.notes"
                    placeholder="Catatan evaluasi atau kendala di kelas..."
                />
            </FormField>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-line">
                <AppButton type="button" variant="ghost" size="sm" @click="emit('close')">
                    Batal
                </AppButton>
                <AppButton type="submit" variant="primary" size="sm" :loading="form.processing">
                    {{ journal ? 'Simpan Perubahan' : 'Terbitkan Jurnal' }}
                </AppButton>
            </div>
        </form>
    </Modal>
</template>

