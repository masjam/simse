<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    student: { type: Object, default: null },
    defaultType: { type: String, default: 'Setoran' },
});

const emit = defineEmits(['close', 'saved']);

const form = useForm({
    student_id: '',
    type: 'Setoran',
    amount: '',
    description: '',
});

watch(() => props.show, (newVal) => {
    if (newVal && props.student) {
        form.reset();
        form.clearErrors();
        form.student_id = props.student.id;
        form.type = props.defaultType;
    }
});

const submit = () => {
    form.post(route('bendahara.dompet.transaction'), {
        preserveScroll: true,
        onSuccess: () => {
            emit('saved');
            emit('close');
        },
    });
};
</script>

<template>
    <Modal
        :show="show"
        title="Transaksi Dompet Digital Siswa"
        max-width="md"
        @close="emit('close')"
    >
        <form @submit.prevent="submit" class="p-6 space-y-4">
            <div v-if="student" class="p-3 rounded-control bg-surface-raised border border-line text-xs">
                <p class="font-bold text-ink">{{ student.name }}</p>
                <p class="text-ink-muted">
                    Saldo saat ini: <span class="font-bold text-brand-700"><MoneyText :amount="student.balance" /></span>
                </p>
            </div>

            <FormField label="Jenis Transaksi" :error="form.errors.type" required>
                <AppSelect
                    v-model="form.type"
                    :options="[
                        { value: 'Setoran', label: 'Setoran (Top-Up Masuk)' },
                        { value: 'Penarikan', label: 'Penarikan Tunai' },
                    ]"
                    required
                />
            </FormField>

            <FormField label="Nominal (Rp)" :error="form.errors.amount" required>
                <AppInput
                    v-model="form.amount"
                    type="number"
                    min="100"
                    placeholder="Contoh: 50000"
                    class="font-numeric"
                    required
                />
            </FormField>

            <FormField label="Keterangan (Opsional)" :error="form.errors.description">
                <AppInput
                    v-model="form.description"
                    placeholder="Contoh: Setoran tabungan pekanan siswa"
                />
            </FormField>

            <div class="flex justify-end gap-2 pt-3 border-t border-line">
                <AppButton type="button" variant="ghost" size="sm" @click="emit('close')">
                    Batal
                </AppButton>
                <AppButton type="submit" variant="primary" size="sm" :loading="form.processing">
                    Simpan Transaksi
                </AppButton>
            </div>
        </form>
    </Modal>
</template>

