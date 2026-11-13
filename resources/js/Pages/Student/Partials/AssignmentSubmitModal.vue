<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import FileInput from '@/Components/ui/FileInput.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    assignment: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close']);

const form = useForm({
    text_answer: props.assignment?.submission?.text_answer || '',
    file: null,
});

const handleFile = (event) => {
    form.file = event.target.files?.[0] || null;
};

const submit = () => {
    if (!props.assignment) return;

    form.post(route('siswa.tugas.submit', props.assignment.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};
</script>

<template>
    <Modal
        :show="show"
        :title="`Kumpulkan Tugas: ${assignment?.title || ''}`"
        max-width="md"
        @close="emit('close')"
    >
        <form v-if="assignment" @submit.prevent="submit" class="space-y-4">
            <div class="p-3 bg-surface-raised rounded-control border border-line text-xs">
                <p class="font-bold text-ink">{{ assignment.title }}</p>
                <p class="text-ink-muted mt-0.5">{{ assignment.subject_name }} &bull; Batas: {{ assignment.due_date }}</p>
            </div>

            <FormField label="Jawaban / Catatan Siswa" :error="form.errors.text_answer">
                <AppTextarea
                    v-model="form.text_answer"
                    rows="4"
                    placeholder="Tuliskan jawaban atau keterangan pengerjaan tugas Anda di sini..."
                />
            </FormField>

            <FormField label="Unggah Berkas Lampiran Tugas (Opsional)" :error="form.errors.file">
                <FileInput
                    @change="form.file = $event"
                />
            </FormField>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-line">
                <AppButton type="button" variant="ghost" size="sm" @click="emit('close')">
                    Batal
                </AppButton>
                <AppButton type="submit" variant="primary" size="sm" :loading="form.processing">
                    Kumpulkan Tugas
                </AppButton>
            </div>
        </form>
    </Modal>
</template>
