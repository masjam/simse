<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppButton from '@/Components/ui/AppButton.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    initialMonth: { type: String, default: 'November' },
    initialYear: { type: String, default: '2026' },
    months: { type: Array, default: () => [] },
});

const emit = defineEmits(['close', 'generated']);

const form = useForm({
    month: props.initialMonth,
    year: props.initialYear,
    base_salary: 3500000,
});

const submit = () => {
    form.post(route('bendahara.payroll.generate'), {
        preserveScroll: true,
        onSuccess: () => {
            emit('generated');
            emit('close');
        },
    });
};
</script>

<template>
    <Modal
        :show="show"
        title="Generate Payroll & Gaji Massal"
        max-width="md"
        @close="emit('close')"
    >
        <form @submit.prevent="submit" class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <FormField label="Bulan" :error="form.errors.month" required>
                    <AppSelect
                        v-model="form.month"
                        :options="months.map(m => ({ value: m, label: m }))"
                        required
                    />
                </FormField>
                <FormField label="Tahun" :error="form.errors.year" required>
                    <AppInput
                        v-model="form.year"
                        placeholder="2026"
                        required
                    />
                </FormField>
            </div>

            <FormField
                label="Gaji Pokok Standar (Rp)"
                help="Tunjangan mengajar dan insentif dihitung otomatis berdasarkan absensi & jadwal KBM."
                :error="form.errors.base_salary"
                required
            >
                <AppInput
                    v-model="form.base_salary"
                    type="number"
                    min="0"
                    placeholder="3500000"
                    class="font-numeric"
                    required
                />
            </FormField>

            <div class="flex justify-end gap-2 pt-3 border-t border-line">
                <AppButton type="button" variant="ghost" size="sm" @click="emit('close')">
                    Batal
                </AppButton>
                <AppButton type="submit" variant="primary" size="sm" :loading="form.processing">
                    Generate Gaji
                </AppButton>
            </div>
        </form>
    </Modal>
</template>

