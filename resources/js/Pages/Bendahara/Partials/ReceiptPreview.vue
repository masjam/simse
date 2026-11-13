<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppButton from '@/Components/ui/AppButton.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';

const props = defineProps({
    student: { type: Object, default: null },
    bill: { type: Object, default: null },
    amount: { type: [Number, String], default: 0 },
    paymentMethod: { type: String, default: 'Tunai' },
    paymentDate: { type: String, default: '' },
});

const page = usePage();

const printReceipt = () => {
    window.print();
};
</script>

<template>
    <div class="bg-surface rounded-card border border-line shadow-card overflow-hidden sticky top-20 flex flex-col">
        <div class="p-6 bg-surface-raised font-mono text-xs border-b border-line flex flex-col space-y-4">
            <div class="text-center pb-2 border-b border-dashed border-line">
                <h4 class="font-bold text-sm text-ink">SD MUHAMMADIYAH AL MUJAHIDIN</h4>
                <p class="text-[11px] text-ink-muted">Wonosari • Gunungkidul</p>
                <p class="text-[10px] font-bold uppercase tracking-wider text-brand-700 mt-1">
                    Tanda Bukti Pembayaran Resmi
                </p>
            </div>

            <div class="space-y-1.5 text-ink">
                <div class="flex justify-between">
                    <span class="text-ink-muted">Tanggal:</span>
                    <span>{{ paymentDate || '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-ink-muted">Kasir:</span>
                    <span>{{ page.props.auth?.user?.name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-ink-muted">Siswa:</span>
                    <span class="font-bold truncate max-w-[150px]">{{ student?.name || '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-ink-muted">Kelas / NISN:</span>
                    <span>{{ student ? `${student.class} • ${student.nisn}` : '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-ink-muted">Metode:</span>
                    <span class="font-semibold">{{ paymentMethod }}</span>
                </div>

                <div class="border-t border-dashed border-line my-3 pt-3 space-y-1">
                    <div class="flex justify-between font-bold text-ink">
                        <span class="truncate max-w-[140px]">{{ bill ? bill.name : 'Item Tagihan' }}</span>
                        <span class="font-numeric">
                            <MoneyText :amount="amount || 0" />
                        </span>
                    </div>
                    <p v-if="bill?.period" class="text-[10px] text-ink-muted">Periode: {{ bill.period }}</p>
                </div>
            </div>

            <div class="border-t-2 border-dashed border-line pt-3">
                <div class="flex justify-between text-sm font-black text-ink font-numeric">
                    <span>TOTAL DITERIMA</span>
                    <span><MoneyText :amount="amount || 0" /></span>
                </div>
            </div>
        </div>

        <div class="p-4 bg-surface">
            <AppButton
                variant="primary"
                class="w-full"
                @click="printReceipt"
            >
                🖨️ Cetak Kuitansi Bukti
            </AppButton>
        </div>
    </div>
</template>

