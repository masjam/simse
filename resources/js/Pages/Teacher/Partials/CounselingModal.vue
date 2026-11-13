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
    students: { type: Array, default: () => [] },
});

const emit = defineEmits(['close', 'created']);

const studentOptions = computed(() => props.students.map(s => ({ value: s.id, label: `${s.name} (${s.class_name || 'Siswa'})` })));

const typeOptions = [
    { value: 'Pribadi', label: 'Bimbingan Pribadi' },
    { value: 'Akademik', label: 'Bimbingan Belajar / Akademik' },
    { value: 'Sosial', label: 'Bimbingan Sosial & Pergaulan' },
    { value: 'Karir', label: 'Bimbingan Minat & Bakat' },
];

const statusOptions = [
    { value: 'Selesai', label: 'Selesai' },
    { value: 'Tindak Lanjut', label: 'Perlu Tindak Lanjut' },
];

const form = useForm({
    student_id: props.students[0]?.id || '',
    type: 'Pribadi',
    date: new Date().toISOString().slice(0, 10),
    problem: '',
    solution: '',
    status: 'Selesai',
});

const submit = () => {
    form.post(route('guru.bk.counseling.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('problem', 'solution');
            emit('created');
            emit('close');
        },
    });
};
</script>

<template>
    <Modal :show="show" title="Catat Sesi Bimbingan Konseling (BK)" max-width="md" @close="emit('close')">
        <form @submit.prevent="submit" class="space-y-3">
            <FormField label="Pilih Siswa" :error="form.errors.student_id" required>
                <AppSelect
                    v-model="form.student_id"
                    :options="studentOptions"
                />
            </FormField>

            <div class="grid grid-cols-2 gap-3">
                <FormField label="Jenis Layanan BK" required>
                    <AppSelect
                        v-model="form.type"
                        :options="typeOptions"
                    />
                </FormField>
                <FormField label="Tanggal Sesi" required>
                    <AppInput
                        v-model="form.date"
                        type="date"
                        required
                    />
                </FormField>
            </div>

            <FormField label="Permasalahan / Bahasan Konseling" :error="form.errors.problem" required>
                <AppTextarea
                    v-model="form.problem"
                    rows="3"
                    placeholder="Pokok persoalan yang dibahas bersama siswa..."
                    required
                />
            </FormField>

            <FormField label="Rekomendasi / Solusi / Kesepakatan" :error="form.errors.solution">
                <AppTextarea
                    v-model="form.solution"
                    rows="2"
                    placeholder="Langkah tindak lanjut atau solusi yang disepakati..."
                />
            </FormField>

            <FormField label="Status Penanganan" required>
                <AppSelect
                    v-model="form.status"
                    :options="statusOptions"
                />
            </FormField>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-line">
                <AppButton type="button" variant="ghost" size="sm" @click="emit('close')">
                    Batal
                </AppButton>
                <AppButton type="submit" variant="primary" size="sm" :loading="form.processing">
                    Simpan Sesi BK
                </AppButton>
            </div>
        </form>
    </Modal>
</template>

