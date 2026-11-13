<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppButton from '@/Components/ui/AppButton.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    editingItem: { type: Object, default: null },
});

const emit = defineEmits(['close', 'saved']);

const periodOptions = [
    { value: 'Bulanan', label: 'Bulanan' },
    { value: 'Semester', label: 'Semester' },
    { value: 'Tahunan', label: 'Tahunan' },
    { value: 'Sekali Bayar', label: 'Sekali Bayar' },
];

const form = useForm({
    name: '',
    amount: '',
    period: 'Bulanan',
});

watch(() => props.show, (newVal) => {
    if (newVal) {
        if (props.editingItem) {
            form.name = props.editingItem.name;
            form.amount = String(props.editingItem.amount);
            form.period = props.editingItem.period;
        } else {
            form.reset();
            form.period = 'Bulanan';
        }
        form.clearErrors();
    }
});

const submit = () => {
    if (props.editingItem) {
        form.put(route('bendahara.spp.bill-types.update', props.editingItem.id), {
            preserveScroll: true,
            onSuccess: () => {
                emit('saved');
                emit('close');
            },
        });
    } else {
        form.post(route('bendahara.spp.bill-types.store'), {
            preserveScroll: true,
            onSuccess: () => {
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
        :title="editingItem ? 'Edit Pos Tagihan Master' : 'Buat Pos Tagihan Master Baru'"
        max-width="md"
        @close="emit('close')"
    >
        <form @submit.prevent="submit" class="p-6 space-y-4">
            <FormField label="Nama Pos Tagihan" :error="form.errors.name" required>
                <AppInput
                    v-model="form.name"
                    placeholder="Contoh: SPP Bulanan / Uang Gedung"
                    required
                />
            </FormField>

            <FormField label="Nominal Baku (Rp)" :error="form.errors.amount" required>
                <AppInput
                    v-model="form.amount"
                    type="number"
                    min="0"
                    placeholder="Contoh: 350000"
                    required
                />
            </FormField>

            <FormField label="Periode Tagihan" :error="form.errors.period" required>
                <AppSelect
                    v-model="form.period"
                    :options="periodOptions"
                    required
                />
            </FormField>

            <div class="flex justify-end gap-2 pt-3 border-t border-line">
                <AppButton type="button" variant="ghost" size="sm" @click="emit('close')">
                    Batal
                </AppButton>
                <AppButton type="submit" variant="primary" size="sm" :loading="form.processing">
                    Simpan Pos Tagihan
                </AppButton>
            </div>
        </form>
    </Modal>
</template>

