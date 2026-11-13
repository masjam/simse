<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import MoneyText from '@/Components/domain/MoneyText.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppInput from '@/Components/ui/AppInput.vue';

const props = defineProps({
    keuangan: {
        type: Object,
        required: true,
    },
    activeChild: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['open-payment-modal']);

const wallet = computed(() => props.keuangan?.wallet || { balance: 0, daily_limit: null });
const bills = computed(() => props.keuangan?.bills || []);
const unpaidBills = computed(() => props.keuangan?.unpaid_bills || []);
const payments = computed(() => props.keuangan?.payments || []);
const todayCanteenSpent = computed(() => props.keuangan?.today_canteen_spent || 0);

const limitForm = useForm({
    student_id: props.activeChild?.id,
    daily_limit: wallet.value.daily_limit ?? '',
});

const saveWalletLimit = () => {
    limitForm.student_id = props.activeChild.id;
    limitForm.post(route('ortu.wallet.limit'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="space-y-4">
        <!-- Saldo & Pengaturan Batas Belanja Kantin -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="bg-surface rounded-card border border-line p-4 shadow-card flex flex-col justify-between">
                <div>
                    <span class="text-xs font-bold text-ink-muted uppercase tracking-wider">
                        Saldo Dompet Digital Siswa
                    </span>
                    <div class="mt-2">
                        <MoneyText :amount="wallet.balance" class="text-2xl sm:text-3xl font-black text-brand-600" />
                    </div>
                    <p class="text-xs text-ink-muted mt-1">
                        Terpakai di Kantin Hari Ini:
                        <strong class="text-ink font-semibold">
                            Rp {{ Number(todayCanteenSpent).toLocaleString('id-ID') }}
                        </strong>
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-line text-[11px] text-ink-muted">
                    Digunakan untuk transaksi tap RFID / QR di e-Kantin sekolah.
                </div>
            </div>

            <!-- Atur Batas Harian -->
            <div class="bg-surface rounded-card border border-line p-4 shadow-card flex flex-col justify-between">
                <div>
                    <span class="text-xs font-bold text-ink-muted uppercase tracking-wider">
                        Batas Belanja Kantin per Hari
                    </span>
                    <p class="text-xs text-ink-muted mt-1">
                        Batasi nominal jajan harian ananda di kantin sekolah. Kosongkan jika tanpa batas.
                    </p>
                    <form @submit.prevent="saveWalletLimit" class="mt-3 flex items-center gap-2">
                        <div class="flex-1">
                            <AppInput
                                v-model="limitForm.daily_limit"
                                type="number"
                                step="1000"
                                min="0"
                                placeholder="Nominal Rp (Kosongkan bila tanpa batas)"
                            />
                        </div>
                        <AppButton
                            type="submit"
                            size="sm"
                            variant="secondary"
                            :loading="limitForm.processing"
                        >
                            Simpan
                        </AppButton>
                    </form>
                </div>
                <p v-if="limitForm.errors.daily_limit" class="text-xs text-status-danger-fg mt-2 font-semibold">
                    {{ limitForm.errors.daily_limit }}
                </p>
            </div>
        </div>

        <!-- Daftar Tagihan SPP & Biaya Sekolah -->
        <div class="bg-surface rounded-card border border-line shadow-card overflow-hidden">
            <div class="p-4 border-b border-line bg-surface-raised flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h4 class="text-sm font-bold text-ink">Tagihan SPP &amp; Biaya Pendidikan</h4>
                    <p class="text-xs text-ink-muted">Status kewajiban administrasi pendidikan ananda.</p>
                </div>
                <AppButton
                    size="sm"
                    variant="primary"
                    @click="emit('open-payment-modal')"
                >
                    Cara Bayar / Info Rekening
                </AppButton>
            </div>

            <div v-if="bills.length === 0" class="p-6">
                <EmptyState
                    title="Tidak Ada Tagihan"
                    description="Belum ada catatan tagihan SPP untuk ananda saat ini."
                />
            </div>
            <div v-else class="divide-y divide-line">
                <div
                    v-for="bill in bills"
                    :key="bill.id"
                    class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 hover:bg-surface-raised/40 transition-colors"
                >
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h5 class="text-sm font-bold text-ink truncate">{{ bill.title }}</h5>
                            <span v-if="bill.period" class="text-xs text-ink-muted bg-surface-raised px-2 py-0.5 rounded-control font-semibold">
                                {{ bill.period }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-ink-muted mt-1">
                            <span>Tagihan: <strong class="text-ink">Rp {{ Number(bill.amount_due).toLocaleString('id-ID') }}</strong></span>
                            <span>Terbayar: <strong class="text-ink">Rp {{ Number(bill.amount_paid).toLocaleString('id-ID') }}</strong></span>
                            <span v-if="bill.remaining > 0" class="text-status-danger-fg font-bold">
                                Sisa: Rp {{ Number(bill.remaining).toLocaleString('id-ID') }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-start sm:self-auto">
                        <StatusBadge :status="bill.status" />
                        <AppButton
                            v-if="bill.status !== 'Lunas'"
                            size="sm"
                            variant="primary"
                            @click="emit('open-payment-modal')"
                        >
                            Bayar
                        </AppButton>
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat Pembayaran Terverifikasi -->
        <div class="bg-surface rounded-card border border-line shadow-card overflow-hidden">
            <div class="p-4 border-b border-line bg-surface-raised">
                <h4 class="text-sm font-bold text-ink">Riwayat Pembayaran</h4>
                <p class="text-xs text-ink-muted">Catatan pembayaran yang telah disetorkan dan diverifikasi kasir.</p>
            </div>

            <div v-if="payments.length === 0" class="p-6">
                <EmptyState
                    title="Belum Ada Riwayat Bayar"
                    description="Belum ada transaksi pembayaran SPP yang tercatat."
                />
            </div>
            <div v-else class="divide-y divide-line">
                <div
                    v-for="payment in payments"
                    :key="payment.id"
                    class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 hover:bg-surface-raised/40 transition-colors"
                >
                    <div>
                        <p class="text-sm font-bold text-ink">
                            {{ payment.bill_title }} ({{ payment.bill_period }})
                        </p>
                        <p class="text-xs text-ink-muted mt-0.5">
                            {{ payment.payment_date }} &bull; Metode: {{ payment.payment_method }} &bull; Kasir: {{ payment.cashier_name }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3 self-start sm:self-auto">
                        <span class="font-bold font-mono text-sm text-brand-600">
                            Rp {{ Number(payment.amount_paid).toLocaleString('id-ID') }}
                        </span>
                        <StatusBadge :status="payment.status" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
