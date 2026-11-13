<script setup>
import Modal from '@/Components/ui/Modal.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';
import KopSurat from '@/Components/domain/KopSurat.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    payroll: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const printSlip = () => {
    window.print();
};
</script>

<template>
    <Modal
        :show="show && Boolean(payroll)"
        title="Slip Gaji Digital Pegawai"
        max-width="md"
        @close="emit('close')"
    >
        <div v-if="payroll" class="p-6 space-y-4 text-xs text-ink max-h-[85vh] overflow-y-auto">
            <KopSurat compact />

            <div class="p-3 bg-surface-raised rounded-control border border-line space-y-1">
                <p class="font-bold text-ink text-sm">{{ payroll.nama }}</p>
                <p class="text-ink-muted">NIP: {{ payroll.nip }} • Periode: {{ payroll.month }} {{ payroll.year }}</p>
                <div class="pt-1">
                    <StatusBadge :variant="payroll.status === 'Dibayar' ? 'success' : (payroll.status === 'Draft' ? 'warning' : 'brand')" size="sm">
                        Status: {{ payroll.status }}
                    </StatusBadge>
                </div>
            </div>

            <div class="divide-y divide-line border border-line rounded-control p-3 bg-surface space-y-1">
                <div
                    v-for="comp in payroll.components"
                    :key="comp.id"
                    class="py-1.5 flex justify-between items-center"
                >
                    <span class="text-ink-muted">{{ comp.name }} ({{ comp.type }})</span>
                    <span
                        class="font-bold font-numeric"
                        :class="comp.type === 'Potongan' ? 'text-status-danger-fg' : 'text-ink'"
                    >
                        {{ comp.type === 'Potongan' ? '-' : '+' }} <MoneyText :amount="comp.amount" />
                    </span>
                </div>
            </div>

            <div class="p-3.5 bg-brand-50 border border-brand-200 rounded-control flex justify-between items-center text-xs">
                <span class="font-bold text-brand-900">Total Gaji Bersih (Take Home):</span>
                <span class="text-base font-black text-brand-900 font-numeric">
                    <MoneyText :amount="payroll.total" />
                </span>
            </div>

            <div class="pt-2">
                <AppButton
                    variant="primary"
                    class="w-full"
                    @click="printSlip"
                >
                    🖨️ Cetak Slip Gaji Resmi
                </AppButton>
            </div>
        </div>
    </Modal>
</template>

