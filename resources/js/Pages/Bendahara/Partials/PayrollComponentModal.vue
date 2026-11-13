<script setup>
import { useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    payroll: { type: Object, default: null },
});

const emit = defineEmits(['close', 'updated']);

const form = useForm({
    type: 'Penerimaan',
    name: '',
    amount: '',
});

const submitComponent = () => {
    if (!props.payroll) return;

    form.post(route('bendahara.payroll.components.store', props.payroll.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.type = 'Penerimaan';
            emit('updated');
        },
    });
};

const deleteComponent = (componentId) => {
    router.delete(route('bendahara.payroll.components.destroy', componentId), {
        preserveScroll: true,
        onSuccess: () => emit('updated'),
    });
};
</script>

<template>
    <Modal
        :show="show && Boolean(payroll)"
        :title="`Komponen Gaji: ${payroll?.nama || ''}`"
        max-width="lg"
        @close="emit('close')"
    >
        <div v-if="payroll" class="p-6 space-y-5 max-h-[85vh] overflow-y-auto">
            <div class="p-3 bg-surface-raised rounded-control border border-line text-xs">
                <p class="font-bold text-ink">{{ payroll.nama }} (NIP: {{ payroll.nip }})</p>
                <p class="text-ink-muted">
                    Periode: {{ payroll.month }} {{ payroll.year }} • Gaji Bersih:
                    <span class="font-bold text-brand-700"><MoneyText :amount="payroll.total" /></span>
                </p>
            </div>

            <!-- Existing Components -->
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-ink uppercase tracking-wider">Daftar Komponen Gaji</h4>
                <div
                    v-for="comp in payroll.components"
                    :key="comp.id"
                    class="flex items-center justify-between p-3 rounded-control bg-surface border border-line text-xs"
                >
                    <div class="flex items-center gap-2">
                        <StatusBadge :variant="comp.type === 'Penerimaan' ? 'success' : 'danger'" size="sm">
                            {{ comp.type }}
                        </StatusBadge>
                        <span class="font-bold text-ink">{{ comp.name }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span
                            class="font-black font-numeric"
                            :class="comp.type === 'Penerimaan' ? 'text-status-success-fg' : 'text-status-danger-fg'"
                        >
                            {{ comp.type === 'Potongan' ? '-' : '+' }} <MoneyText :amount="comp.amount" />
                        </span>
                        <AppButton
                            variant="ghost"
                            size="sm"
                            class="text-status-danger-fg"
                            @click="deleteComponent(comp.id)"
                        >
                            Hapus
                        </AppButton>
                    </div>
                </div>
            </div>

            <!-- Add Component Form -->
            <form @submit.prevent="submitComponent" class="space-y-3 border-t border-line pt-4">
                <p class="text-xs font-bold uppercase text-ink-muted">
                    Tambah Komponen Baru (Bonus / Kasbon / Insentif)
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <FormField label="Jenis" required>
                        <AppSelect
                            v-model="form.type"
                            :options="[
                                { value: 'Penerimaan', label: 'Penerimaan (+)' },
                                { value: 'Potongan', label: 'Potongan (-)' },
                            ]"
                            required
                        />
                    </FormField>
                    <div class="sm:col-span-2">
                        <FormField label="Nama Komponen" :error="form.errors.name" required>
                            <AppInput
                                v-model="form.name"
                                placeholder="Contoh: Insentif Kehadiran Sholat"
                                required
                            />
                        </FormField>
                    </div>
                </div>

                <div class="flex gap-2 items-end">
                    <div class="flex-1">
                        <FormField label="Nominal (Rp)" :error="form.errors.amount" required>
                            <AppInput
                                v-model="form.amount"
                                type="number"
                                min="1"
                                placeholder="Contoh: 150000"
                                class="font-numeric"
                                required
                            />
                        </FormField>
                    </div>
                    <AppButton
                        type="submit"
                        variant="primary"
                        size="sm"
                        :loading="form.processing"
                    >
                        + Tambah Komponen
                    </AppButton>
                </div>
            </form>
        </div>
    </Modal>
</template>

