<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppRadio from '@/Components/ui/AppRadio.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    exam: { type: Object, default: null },
});

const emit = defineEmits(['close', 'created']);

const typeOptions = [
    { value: 'Pilihan Ganda', label: 'Pilihan Ganda (A-D)' },
    { value: 'Benar Salah', label: 'Benar / Salah' },
    { value: 'Esai', label: 'Esai / Uraian' },
];

const form = useForm({
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

const correctOptionIndex = computed({
    get: () => {
        const idx = form.options.findIndex(o => o.is_correct);
        return idx !== -1 ? idx : 0;
    },
    set: (val) => {
        form.options.forEach((opt, i) => {
            opt.is_correct = i === Number(val);
        });
    },
});

const submit = () => {
    if (!props.exam) return;

    form.post(route('guru.cbt.question.store', props.exam.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('content');
            form.options.forEach((opt, idx) => {
                opt.content = '';
                opt.is_correct = idx === 0;
            });
            emit('created');
            emit('close');
        },
    });
};
</script>

<template>
    <Modal :show="show" :title="`Tambah Soal Ujian: ${exam?.title || ''}`" max-width="lg" @close="emit('close')">
        <form @submit.prevent="submit" class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <FormField label="Tipe Butir Soal" required>
                    <AppSelect
                        v-model="form.type"
                        :options="typeOptions"
                    />
                </FormField>
                <FormField label="Bobot Nilai" required>
                    <AppInput
                        v-model="form.marks"
                        type="number"
                        min="1"
                        max="100"
                        required
                    />
                </FormField>
            </div>

            <FormField label="Isi Soal / Pertanyaan" :error="form.errors.content" required>
                <AppTextarea
                    v-model="form.content"
                    rows="3"
                    placeholder="Tuliskan butir soal dengan jelas..."
                    required
                />
            </FormField>

            <!-- Options for Pilihan Ganda -->
            <div v-if="form.type === 'Pilihan Ganda'" class="space-y-2 pt-1">
                <label class="block text-xs font-bold text-ink-muted">Pilihan Jawaban (Pilih radio untuk kunci benar):</label>
                <div
                    v-for="(opt, idx) in form.options"
                    :key="idx"
                    class="flex items-center gap-2 p-2 rounded-control bg-surface-raised border border-line"
                >
                    <AppRadio
                        v-model="correctOptionIndex"
                        :value="idx"
                        name="correct-option-pg"
                    />
                    <span class="w-6 font-bold font-mono text-xs text-ink shrink-0">
                        {{ String.fromCharCode(65 + idx) }}.
                    </span>
                    <AppInput
                        v-model="opt.content"
                        :placeholder="`Isi pilihan ${String.fromCharCode(65 + idx)}`"
                        required
                    />
                </div>
            </div>

            <!-- Options for Benar Salah -->
            <div v-else-if="form.type === 'Benar Salah'" class="space-y-2 pt-1">
                <label class="block text-xs font-bold text-ink-muted">Kunci Jawaban Benar:</label>
                <div class="flex items-center gap-4 text-xs font-bold text-ink">
                    <AppRadio
                        v-model="correctOptionIndex"
                        :value="0"
                        name="correct-option-bs"
                        label="Pernyataan Benar"
                    />
                    <AppRadio
                        v-model="correctOptionIndex"
                        :value="1"
                        name="correct-option-bs"
                        label="Pernyataan Salah"
                    />
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-line">
                <AppButton type="button" variant="ghost" size="sm" @click="emit('close')">
                    Batal
                </AppButton>
                <AppButton type="submit" variant="primary" size="sm" :loading="form.processing">
                    Simpan Butir Soal
                </AppButton>
            </div>
        </form>
    </Modal>
</template>
