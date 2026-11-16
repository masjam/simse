<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import FileInput from '@/Components/ui/FileInput.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    exam: { type: Object, default: null },
});

const emit = defineEmits(['close', 'imported']);

const form = useForm({
    file: null,
});

const submit = () => {
    if (!props.exam || !form.file) return;

    form.post(route('guru.cbt.question.import', props.exam.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('imported');
            emit('close');
        },
    });
};
</script>

<template>
    <Modal :show="show" :title="`Impor Soal Excel: ${exam?.title || ''}`" max-width="md" @close="emit('close')">
        <form @submit.prevent="submit" class="space-y-4">
            <div class="p-3 rounded-control bg-brand-50 border border-brand-200 text-xs text-brand-900 space-y-1.5">
                <p class="font-bold">Format Kolom Excel yang Didukung:</p>
                <p class="text-[11px] font-mono leading-relaxed text-brand-800">
                    tipe_soal &bull; soal &bull; bobot &bull; opsi_a &bull; opsi_b &bull; opsi_c &bull; opsi_d &bull; kunci_jawaban
                </p>
                <p class="text-[11px] text-ink-muted pt-1">
                    Mendukung 6 tipe: <em>Pilihan Ganda, Ganda Kompleks, Benar Salah, Menjodohkan, Isian Rumpang, Esai</em>.
                </p>
            </div>

            <FormField label="Pilih Berkas Excel / CSV (.xlsx, .xls, .csv)" :error="form.errors.file" required>
                <FileInput
                    v-model="form.file"
                    accept=".xlsx,.xls,.csv"
                />
            </FormField>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-line">
                <AppButton type="button" variant="ghost" size="sm" @click="emit('close')">
                    Batal
                </AppButton>
                <AppButton
                    type="submit"
                    variant="primary"
                    size="sm"
                    :loading="form.processing"
                    :disabled="!form.file"
                >
                    Mulai Impor Soal
                </AppButton>
            </div>
        </form>
    </Modal>
</template>
