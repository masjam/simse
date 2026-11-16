<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppRadio from '@/Components/ui/AppRadio.vue';
import AppCheckbox from '@/Components/ui/AppCheckbox.vue';
import FileInput from '@/Components/ui/FileInput.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    show: { type: Boolean, default: false },
    exam: { type: Object, default: null },
    questionToEdit: { type: Object, default: null },
});

const emit = defineEmits(['close', 'saved']);

const toast = useToast();

const typeOptions = [
    { value: 'Pilihan Ganda', label: '1. Pilihan Ganda (Satu Jawaban Benar)' },
    { value: 'Ganda Kompleks', label: '2. Ganda Kompleks (Bisa > 1 Jawaban Benar)' },
    { value: 'Benar Salah', label: '3. Benar / Salah' },
    { value: 'Menjodohkan', label: '4. Menjodohkan (Premis -> Pasangan)' },
    { value: 'Isian Rumpang', label: '5. Isian Rumpang (Kata / Angka Tepat)' },
    { value: 'Esai', label: '6. Esai / Uraian (Koreksi Manual Guru)' },
];

const showPreview = ref(false);

const form = useForm({
    type: 'Pilihan Ganda',
    content: '',
    marks: 10,
    image: null,
    metadata: {
        blanks: [{ accepted: [''] }],
    },
    options: [
        { content: '', is_correct: true, pair_match: '' },
        { content: '', is_correct: false, pair_match: '' },
        { content: '', is_correct: false, pair_match: '' },
        { content: '', is_correct: false, pair_match: '' },
    ],
});

// Sync data when editing
watch(() => props.questionToEdit, (q) => {
    if (q) {
        form.type = q.type;
        form.content = q.content;
        form.marks = q.marks;
        form.metadata = q.metadata || { blanks: [{ accepted: [''] }] };
        form.options = q.options && q.options.length ? q.options.map(o => ({
            id: o.id,
            content: o.content,
            is_correct: Boolean(o.is_correct),
            pair_match: o.pair_match || '',
        })) : [];
    } else {
        form.reset();
        form.type = 'Pilihan Ganda';
        form.options = [
            { content: '', is_correct: true, pair_match: '' },
            { content: '', is_correct: false, pair_match: '' },
            { content: '', is_correct: false, pair_match: '' },
            { content: '', is_correct: false, pair_match: '' },
        ];
    }
}, { immediate: true });

// Type change setup
watch(() => form.type, (newType) => {
    if (props.questionToEdit) return;

    if (newType === 'Menjodohkan') {
        form.options = [
            { content: 'Premis 1', pair_match: 'Pasangan 1', is_correct: true },
            { content: 'Premis 2', pair_match: 'Pasangan 2', is_correct: true },
        ];
    } else if (newType === 'Benar Salah') {
        form.options = [
            { content: 'Benar', is_correct: true },
            { content: 'Salah', is_correct: false },
        ];
    } else if (newType === 'Isian Rumpang') {
        form.metadata = { blanks: [{ accepted: [''] }] };
        form.options = [];
    } else if (newType === 'Esai') {
        form.options = [];
    } else if (newType === 'Pilihan Ganda' || newType === 'Ganda Kompleks') {
        if (form.options.length < 2) {
            form.options = [
                { content: '', is_correct: true },
                { content: '', is_correct: false },
                { content: '', is_correct: false },
                { content: '', is_correct: false },
            ];
        }
    }
});

// Single choice radio helper
const singleChoiceIndex = computed({
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

// Helpers for dynamic option lists
const addOption = () => {
    form.options.push({ content: '', is_correct: false, pair_match: '' });
};

const removeOption = (idx) => {
    if (form.options.length > 2) {
        form.options.splice(idx, 1);
    }
};

const addBlank = () => {
    if (!form.metadata.blanks) form.metadata.blanks = [];
    form.metadata.blanks.push({ accepted: [''], ignore_punct: true });
};

const removeBlank = (idx) => {
    if (form.metadata.blanks.length > 1) {
        form.metadata.blanks.splice(idx, 1);
    }
};

const submit = () => {
    if (!props.exam) return;

    // Validation checks
    if (form.type === 'Ganda Kompleks') {
        const hasCorrect = form.options.some(o => o.is_correct);
        if (!hasCorrect) {
            toast.error('Ganda Kompleks wajib memiliki minimal satu jawaban benar yang dicentang.');
            return;
        }
    }

    if (props.questionToEdit) {
        form.put(route('guru.cbt.question.update', props.questionToEdit.id), {
            preserveScroll: true,
            onSuccess: () => {
                emit('saved');
                emit('close');
            },
        });
    } else {
        form.post(route('guru.cbt.question.store', props.exam.id), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset('content');
                emit('saved');
                emit('close');
            },
        });
    }
};
</script>

<template>
    <Modal
        :show="show"
        :title="questionToEdit ? 'Ubah Butir Soal CBT' : `Tambah Soal Baru: ${exam?.title || ''}`"
        max-width="2xl"
        @close="emit('close')"
    >
        <form @submit.prevent="submit" class="space-y-4">
            <!-- Header Controls: Type & Marks -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <FormField label="Tipe Butir Soal" required>
                        <AppSelect
                            v-model="form.type"
                            :options="typeOptions"
                        />
                    </FormField>
                </div>
                <div>
                    <FormField label="Bobot Nilai (Poin)" required>
                        <AppInput
                            v-model="form.marks"
                            type="number"
                            min="1"
                            max="100"
                            required
                        />
                    </FormField>
                </div>
            </div>

            <!-- Question Content -->
            <FormField label="Isi Soal / Pertanyaan" :error="form.errors.content" required>
                <AppTextarea
                    v-model="form.content"
                    rows="3"
                    placeholder="Tuliskan butir soal dengan jelas..."
                    required
                />
            </FormField>

            <!-- Question Image Upload -->
            <div class="p-3 rounded-control bg-surface-raised border border-line space-y-2">
                <div class="flex items-center justify-between text-xs font-bold text-ink">
                    <span>Lampiran Gambar Soal (Opsional, Maks 2MB)</span>
                    <AppButton
                        type="button"
                        size="sm"
                        variant="ghost"
                        class="!px-1 !py-0.5 text-xs text-brand-600"
                        @click="showPreview = !showPreview"
                    >
                        {{ showPreview ? 'Sembunyikan Pratinjau' : 'Lihat Pratinjau Siswa' }}
                    </AppButton>
                </div>
                <FileInput
                    v-model="form.image"
                    accept="image/*"
                />
            </div>

            <!-- Dynamic Fields per Type -->

            <!-- 1. Pilihan Ganda -->
            <div v-if="form.type === 'Pilihan Ganda'" class="space-y-2 pt-1">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-ink-muted">Pilihan Jawaban (Radio = Kunci Benar):</label>
                    <AppButton type="button" size="sm" variant="ghost" class="!px-1 !py-0.5 text-xs text-brand-600 font-bold" @click="addOption">
                        + Tambah Opsi
                    </AppButton>
                </div>
                <div
                    v-for="(opt, idx) in form.options"
                    :key="idx"
                    class="flex items-center gap-2 p-2 rounded-control bg-surface-raised border border-line"
                >
                    <AppRadio
                        v-model="singleChoiceIndex"
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
                    <AppButton
                        v-if="form.options.length > 2"
                        type="button"
                        size="sm"
                        variant="ghost"
                        class="!px-1.5 !py-0.5 text-xs text-ink-muted hover:text-status-danger-fg"
                        @click="removeOption(idx)"
                    >
                        ✕
                    </AppButton>
                </div>
            </div>

            <!-- 2. Ganda Kompleks -->
            <div v-else-if="form.type === 'Ganda Kompleks'" class="space-y-2 pt-1">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-ink-muted">Pilihan Jawaban (Centang semua opsi yang benar):</label>
                    <AppButton type="button" size="sm" variant="ghost" class="!px-1 !py-0.5 text-xs text-brand-600 font-bold" @click="addOption">
                        + Tambah Opsi
                    </AppButton>
                </div>
                <div
                    v-for="(opt, idx) in form.options"
                    :key="idx"
                    class="flex items-center gap-2 p-2 rounded-control bg-surface-raised border border-line"
                >
                    <AppCheckbox
                        v-model="opt.is_correct"
                    />
                    <span class="w-6 font-bold font-mono text-xs text-ink shrink-0">
                        {{ String.fromCharCode(65 + idx) }}.
                    </span>
                    <AppInput
                        v-model="opt.content"
                        :placeholder="`Isi pilihan ${String.fromCharCode(65 + idx)}`"
                        required
                    />
                    <AppButton
                        v-if="form.options.length > 2"
                        type="button"
                        size="sm"
                        variant="ghost"
                        class="!px-1.5 !py-0.5 text-xs text-ink-muted hover:text-status-danger-fg"
                        @click="removeOption(idx)"
                    >
                        ✕
                    </AppButton>
                </div>
            </div>

            <!-- 3. Benar Salah -->
            <div v-else-if="form.type === 'Benar Salah'" class="space-y-2 pt-1">
                <label class="block text-xs font-bold text-ink-muted">Kunci Jawaban Pernyataan:</label>
                <div class="flex items-center gap-4 text-xs font-bold text-ink">
                    <AppRadio
                        v-model="singleChoiceIndex"
                        :value="0"
                        name="correct-option-bs"
                        label="Pernyataan Benar"
                    />
                    <AppRadio
                        v-model="singleChoiceIndex"
                        :value="1"
                        name="correct-option-bs"
                        label="Pernyataan Salah"
                    />
                </div>
            </div>

            <!-- 4. Menjodohkan -->
            <div v-else-if="form.type === 'Menjodohkan'" class="space-y-2 pt-1">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-ink-muted">Pasangan Benar (Premis Kiri -> Pasangan Kanan):</label>
                    <AppButton type="button" size="sm" variant="ghost" class="!px-1 !py-0.5 text-xs text-brand-600 font-bold" @click="addOption">
                        + Tambah Baris Pasangan
                    </AppButton>
                </div>
                <div
                    v-for="(opt, idx) in form.options"
                    :key="idx"
                    class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-2.5 rounded-control bg-surface-raised border border-line items-center"
                >
                    <div class="flex items-center gap-1.5">
                        <span class="w-5 text-center font-mono font-bold text-xs text-ink-muted">{{ idx + 1 }}.</span>
                        <AppInput
                            v-model="opt.content"
                            placeholder="Premis / Pernyataan Kiri"
                            required
                        />
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-bold text-ink-muted">→</span>
                        <AppInput
                            v-model="opt.pair_match"
                            placeholder="Kunci Pasangan Kanan"
                            required
                        />
                        <AppButton
                            v-if="form.options.length > 2"
                            type="button"
                            size="sm"
                            variant="ghost"
                            class="!px-1.5 !py-0.5 text-xs text-ink-muted hover:text-status-danger-fg"
                            @click="removeOption(idx)"
                        >
                            ✕
                        </AppButton>
                    </div>
                </div>
            </div>

            <!-- 5. Isian Rumpang -->
            <div v-else-if="form.type === 'Isian Rumpang'" class="space-y-2 pt-1">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-ink-muted">Daftar Bagian Rumpang (Kunci yang diterima):</label>
                    <AppButton type="button" size="sm" variant="ghost" class="!px-1 !py-0.5 text-xs text-brand-600 font-bold" @click="addBlank">
                        + Tambah Rumpang
                    </AppButton>
                </div>
                <div
                    v-for="(blank, idx) in form.metadata.blanks"
                    :key="idx"
                    class="p-2.5 rounded-control bg-surface-raised border border-line space-y-2"
                >
                    <div class="flex items-center justify-between text-xs font-bold text-ink">
                        <span>Rumpang [{{ idx + 1 }}]</span>
                        <AppButton
                            v-if="form.metadata.blanks.length > 1"
                            type="button"
                            size="sm"
                            variant="ghost"
                            class="!px-1 !py-0.5 text-status-danger-fg text-[11px]"
                            @click="removeBlank(idx)"
                        >
                            Hapus Rumpang
                        </AppButton>
                    </div>
                    <AppInput
                        :model-value="Array.isArray(blank.accepted) ? blank.accepted.join('|') : blank.accepted"
                        placeholder="Contoh: yogyakarta|jogja (gunakan pemisah | jika ada alternatif)"
                        @update:model-value="blank.accepted = $event.split('|')"
                        required
                    />
                    <p class="text-[10px] text-ink-muted">
                        Pemeriksaan mengabaikan huruf besar/kecil, spasi ganda, tanda baca, serta menoleransi koma desimal (2,5 = 2.5).
                    </p>
                </div>
            </div>

            <!-- 6. Esai -->
            <div v-else-if="form.type === 'Esai'" class="p-3 rounded-control bg-status-warning-bg border border-status-warning-line text-xs text-status-warning-fg">
                <strong>Catatan Esai:</strong> Jawaban siswa akan masuk ke antrean koreksi manual guru di menu <em>Koreksi Esai</em>. Guru dapat memberikan skor hingga maksimal <strong>{{ form.marks }} poin</strong> beserta catatan feedback.
            </div>

            <!-- Live Student Preview Box -->
            <div v-if="showPreview" class="p-4 rounded-card border-2 border-dashed border-brand-300 bg-brand-50/20 space-y-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-brand-700 block">
                    Pratinjau Tampilan Siswa
                </span>
                <p class="text-sm font-medium text-ink whitespace-pre-line">{{ form.content }}</p>
            </div>

            <!-- Modal Footer Actions -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-line">
                <AppButton type="button" variant="ghost" size="sm" @click="emit('close')">
                    Batal
                </AppButton>
                <AppButton type="submit" variant="primary" size="sm" :loading="form.processing">
                    {{ questionToEdit ? 'Perbarui Soal' : 'Simpan Butir Soal' }}
                </AppButton>
            </div>
        </form>
    </Modal>
</template>
