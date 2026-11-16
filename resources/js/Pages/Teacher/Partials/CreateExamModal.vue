<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppCheckbox from '@/Components/ui/AppCheckbox.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    subjects: { type: Array, default: () => [] },
    students: { type: Array, default: () => [] },
});

const emit = defineEmits(['close', 'created']);

const subjectOptions = computed(() => props.subjects.map(s => ({ value: s, label: s })));

const scoringRuleOptions = [
    { value: 'standard', label: 'Standar (Semua kunci benar = nilai penuh, salah = 0)' },
    { value: 'partial', label: 'Proporsional Parsial (Skor proporsional, salah mengurangi)' },
];

const form = useForm({
    title: '',
    subject_name: props.subjects[0] || 'Pendidikan Agama Islam dan Budi Pekerti',
    start_time: new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString().slice(0, 16),
    end_time: new Date(Date.now() + 26 * 60 * 60 * 1000).toISOString().slice(0, 16),
    duration_minutes: 60,
    max_violations: 3,
    scoring_rule: 'standard',
    randomize_questions: true,
    randomize_options: true,
    is_remedial: false,
    remedial_student_ids: [],
    description: '',
});

const toggleRemedialStudent = (studentId) => {
    const idx = form.remedial_student_ids.indexOf(studentId);
    if (idx > -1) {
        form.remedial_student_ids.splice(idx, 1);
    } else {
        form.remedial_student_ids.push(studentId);
    }
};

const submit = () => {
    form.post(route('guru.cbt.exam.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('created');
            emit('close');
        },
    });
};
</script>

<template>
    <Modal :show="show" title="Buat Paket Ujian CBT Baru" max-width="lg" @close="emit('close')">
        <form @submit.prevent="submit" class="space-y-3">
            <FormField label="Mata Pelajaran" :error="form.errors.subject_name" required>
                <AppSelect
                    v-model="form.subject_name"
                    :options="subjectOptions"
                />
            </FormField>

            <FormField label="Judul Paket Ujian" :error="form.errors.title" required>
                <AppInput
                    v-model="form.title"
                    placeholder="Contoh: Penilaian Harian PAI Bab 1"
                    required
                />
            </FormField>

            <div class="grid grid-cols-2 gap-3">
                <FormField label="Waktu Mulai" :error="form.errors.start_time" required>
                    <AppInput
                        v-model="form.start_time"
                        type="datetime-local"
                        required
                    />
                </FormField>
                <FormField label="Waktu Berakhir" :error="form.errors.end_time" required>
                    <AppInput
                        v-model="form.end_time"
                        type="datetime-local"
                        required
                    />
                </FormField>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <FormField label="Durasi Pengerjaan (Menit)" :error="form.errors.duration_minutes" required>
                    <AppInput
                        v-model="form.duration_minutes"
                        type="number"
                        min="5"
                        required
                    />
                </FormField>
                <FormField label="Maksimal Pelanggaran" :error="form.errors.max_violations" required>
                    <AppInput
                        v-model="form.max_violations"
                        type="number"
                        min="1"
                        required
                    />
                </FormField>
            </div>

            <FormField label="Aturan Penilaian (Scoring Rule)" required>
                <AppSelect
                    v-model="form.scoring_rule"
                    :options="scoringRuleOptions"
                />
            </FormField>

            <!-- Randomization Toggles -->
            <div class="grid grid-cols-2 gap-3 p-2.5 rounded-control bg-surface-raised border border-line">
                <AppCheckbox v-model="form.randomize_questions" label="Acak Urutan Soal" />
                <AppCheckbox v-model="form.randomize_options" label="Acak Pilihan Opsi" />
            </div>

            <!-- Remedial Option -->
            <div class="p-2.5 rounded-control bg-surface-raised border border-line space-y-2">
                <AppCheckbox v-model="form.is_remedial" label="Paket Ujian Remedial (Hanya Siswa Sasaran Tertentu)" />

                <div v-if="form.is_remedial" class="space-y-1.5 pt-1">
                    <span class="text-[11px] text-ink-muted block font-semibold">Pilih Siswa Sasaran Remedial:</span>
                    <div class="max-h-36 overflow-y-auto border border-line rounded-control p-2 space-y-1 bg-surface">
                        <div
                            v-for="s in students"
                            :key="s.id"
                            class="flex items-center gap-2 text-xs text-ink hover:bg-surface-raised p-1 rounded"
                        >
                            <AppCheckbox
                                :model-value="form.remedial_student_ids"
                                :value="s.id"
                                :label="`${s.name} (${s.nis} - ${s.class})`"
                                @update:model-value="form.remedial_student_ids = $event"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <FormField label="Petunjuk / Tata Tertib Ujian" :error="form.errors.description">
                <AppTextarea
                    v-model="form.description"
                    rows="2"
                    placeholder="Petunjuk khusus pengerjaan soal untuk siswa..."
                />
            </FormField>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-line">
                <AppButton type="button" variant="ghost" size="sm" @click="emit('close')">
                    Batal
                </AppButton>
                <AppButton type="submit" variant="primary" size="sm" :loading="form.processing">
                    Jadwalkan Ujian
                </AppButton>
            </div>
        </form>
    </Modal>
</template>
