<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/ui/Modal.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';
import FormField from '@/Components/ui/FormField.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    postRoute: { type: String, required: true },
});

const emit = defineEmits(['close']);

const form = useForm({
    amount: '',
    installment_count: 3,
    reason: '',
});

const installmentOptions = [
    { value: 1, label: '1 Bulan (Langsung Potong Bulan Depan)' },
    { value: 2, label: '2 Bulan' },
    { value: 3, label: '3 Bulan' },
    { value: 6, label: '6 Bulan' },
    { value: 10, label: '10 Bulan' },
    { value: 12, label: '12 Bulan (Maksimal)' },
];

const estimatedMonthly = computed(() => {
    const amt = parseFloat(form.amount) || 0;
    const months = parseInt(form.installment_count) || 1;
    if (amt <= 0 || months <= 0) return 0;
    return Math.round(amt / months);
});

const submit = () => {
    form.post(props.postRoute, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};
</script>

<template>
    <Modal :show="show" max-width="md" @close="emit('close')">
        <div class="p-5 space-y-4">
            <div>
                <h3 class="text-base font-bold text-ink">Formulir Pengajuan Kasbon Pegawai</h3>
                <p class="text-xs text-ink-muted mt-0.5">
                    Pengajuan pinjaman kasbon akan ditinjau dan disetujui oleh Bendahara Sekolah.
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-3.5">
                <FormField label="Nominal Pinjaman Kasbon (Rp)" :error="form.errors.amount" required>
                    <AppInput
                        v-model="form.amount"
                        type="number"
                        placeholder="Contoh: 1500000"
                        min="100000"
                        max="20000000"
                        step="50000"
                        required
                    />
                </FormField>

                <FormField label="Jangka Waktu Angsuran (Tenor)" :error="form.errors.installment_count" required>
                    <AppSelect
                        v-model="form.installment_count"
                        :options="installmentOptions"
                        required
                    />
                </FormField>

                <div v-if="estimatedMonthly > 0" class="p-3 rounded-control bg-surface-raised border border-line text-xs">
                    <span class="text-ink-muted block">Estimasi Potongan Gaji Bulanan:</span>
                    <strong class="text-brand-700 font-bold font-mono text-sm">
                        Rp {{ estimatedMonthly.toLocaleString('id-ID') }} / bulan
                    </strong>
                </div>

                <FormField label="Keperluan / Alasan Pengajuan" :error="form.errors.reason" required>
                    <AppTextarea
                        v-model="form.reason"
                        placeholder="Jelaskan kebutuhan kasbon (contoh: Biaya perbaikan kendaraan operasional, kebutuhan darurat keluarga, dll)"
                        rows="3"
                        required
                    />
                </FormField>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-line">
                    <AppButton type="button" variant="secondary" @click="emit('close')">
                        Batal
                    </AppButton>
                    <AppButton type="submit" variant="primary" :loading="form.processing">
                        Kirim Pengajuan
                    </AppButton>
                </div>
            </form>
        </div>
    </Modal>
</template>

