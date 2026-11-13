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
    violationPoints: { type: Array, default: () => [] },
});

const emit = defineEmits(['close', 'created']);

const studentOptions = computed(() => props.students.map(s => ({ value: s.id, label: `${s.name} (${s.class_name || 'Siswa'})` })));
const pointOptions = computed(() => props.violationPoints.map(p => ({ value: p.id, label: `${p.name} (+${p.points} Poin)` })));

const form = useForm({
    student_id: props.students[0]?.id || '',
    violation_point_id: props.violationPoints[0]?.id || '',
    date: new Date().toISOString().slice(0, 10),
    description: '',
});

const submit = () => {
    form.post(route('guru.bk.violation.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('description');
            emit('created');
            emit('close');
        },
    });
};
</script>

<template>
    <Modal :show="show" title="Catat Pelanggaran / Ketertiban Siswa" max-width="md" @close="emit('close')">
        <form @submit.prevent="submit" class="space-y-3">
            <FormField label="Pilih Siswa" :error="form.errors.student_id" required>
                <AppSelect
                    v-model="form.student_id"
                    :options="studentOptions"
                />
            </FormField>

            <FormField label="Jenis Pelanggaran / Tata Tertib" :error="form.errors.violation_point_id" required>
                <AppSelect
                    v-model="form.violation_point_id"
                    :options="pointOptions"
                />
            </FormField>

            <FormField label="Tanggal Kejadian" :error="form.errors.date" required>
                <AppInput
                    v-model="form.date"
                    type="date"
                    required
                />
            </FormField>

            <FormField label="Uraian Kronologi / Catatan Kejadian" :error="form.errors.description">
                <AppTextarea
                    v-model="form.description"
                    rows="3"
                    placeholder="Tuliskan keterangan kejadian secara jelas..."
                />
            </FormField>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-line">
                <AppButton type="button" variant="ghost" size="sm" @click="emit('close')">
                    Batal
                </AppButton>
                <AppButton type="submit" variant="primary" size="sm" :loading="form.processing">
                    Simpan Catatan
                </AppButton>
            </div>
        </form>
    </Modal>
</template>

