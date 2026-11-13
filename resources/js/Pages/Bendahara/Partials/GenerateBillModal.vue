<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppButton from '@/Components/ui/AppButton.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    billTypes: { type: Array, default: () => [] },
    classes: { type: Array, default: () => [] },
    initialBillTypeId: { type: [String, Number], default: null },
});

const emit = defineEmits(['close', 'generated']);

const monthsList = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

const form = useForm({
    bill_type_id: '',
    student_class_id: '',
    month: 'November',
    year: String(new Date().getFullYear()),
    amount_due: '',
});

const billTypeOptions = computed(() => {
    return props.billTypes.map((bt) => ({
        value: bt.id,
        label: `${bt.name} (${bt.period} - Rp ${Number(bt.amount).toLocaleString('id-ID')})`,
    }));
});

const classOptions = computed(() => {
    return [
        { value: '', label: 'Semua Kelas (Seluruh Siswa)' },
        ...props.classes.map((c) => ({ value: c.id, label: `Kelas ${c.name}` })),
    ];
});

const selectedBillType = computed(() => {
    return props.billTypes.find((bt) => bt.id === form.bill_type_id) || null;
});

const updateMonthByPeriod = (billTypeId) => {
    const selected = props.billTypes.find((bt) => bt.id === billTypeId);
    if (selected?.period === 'Semester') {
        form.month = 'Semester Ganjil';
    } else if (selected?.period === 'Bulanan') {
        const curMonthIdx = new Date().getMonth();
        form.month = monthsList[curMonthIdx] || 'Januari';
    } else {
        form.month = '';
    }
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        form.bill_type_id = props.initialBillTypeId || props.billTypes[0]?.id || '';
        form.amount_due = '';
        form.student_class_id = '';
        updateMonthByPeriod(form.bill_type_id);
        form.clearErrors();
    }
});

watch(() => form.bill_type_id, (newId) => {
    updateMonthByPeriod(newId);
});

const submit = () => {
    form.post(route('bendahara.spp.generate'), {
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
        title="Distribusi Tagihan Siswa Massal"
        max-width="md"
        @close="emit('close')"
    >
        <form @submit.prevent="submit" class="p-6 space-y-4">
            <FormField label="Pos Tagihan" :error="form.errors.bill_type_id" required>
                <AppSelect
                    v-model="form.bill_type_id"
                    :options="billTypeOptions"
                    required
                />
            </FormField>

            <FormField label="Target Kelas" :error="form.errors.student_class_id">
                <AppSelect
                    v-model="form.student_class_id"
                    :options="classOptions"
                />
            </FormField>

            <div class="grid grid-cols-2 gap-3">
                <div v-if="selectedBillType?.period === 'Semester'">
                    <FormField label="Pilih Semester" :error="form.errors.month" required>
                        <AppSelect
                            v-model="form.month"
                            :options="[
                                { value: 'Semester Ganjil', label: 'Semester Ganjil' },
                                { value: 'Semester Genap', label: 'Semester Genap' },
                                { value: 'Semester 1', label: 'Semester 1' },
                                { value: 'Semester 2', label: 'Semester 2' },
                            ]"
                            required
                        />
                    </FormField>
                </div>
                <div v-else-if="selectedBillType?.period === 'Bulanan'">
                    <FormField label="Pilih Bulan" :error="form.errors.month" required>
                        <AppSelect
                            v-model="form.month"
                            :options="monthsList.map(m => ({ value: m, label: m }))"
                            required
                        />
                    </FormField>
                </div>
                <div v-else>
                    <FormField label="Keterangan Sub-Periode" :error="form.errors.month">
                        <AppInput
                            v-model="form.month"
                            placeholder="Contoh: Tahap 1 / T.A. 2026/2027"
                        />
                    </FormField>
                </div>

                <FormField label="Tahun" :error="form.errors.year" required>
                    <AppInput
                        v-model="form.year"
                        placeholder="Contoh: 2026"
                        required
                    />
                </FormField>
            </div>

            <FormField
                label="Nominal Khusus (Opsional)"
                help="Kosongkan jika ingin menggunakan nominal baku pos tagihan."
                :error="form.errors.amount_due"
            >
                <AppInput
                    v-model="form.amount_due"
                    type="number"
                    min="1"
                    placeholder="Kosongkan untuk nominal baku"
                />
            </FormField>

            <div class="flex justify-end gap-2 pt-3 border-t border-line">
                <AppButton type="button" variant="ghost" size="sm" @click="emit('close')">
                    Batal
                </AppButton>
                <AppButton type="submit" variant="primary" size="sm" :loading="form.processing">
                    Terbitkan Tagihan Massal
                </AppButton>
            </div>
        </form>
    </Modal>
</template>

