<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import { useFormat } from '@/composables/useFormat';

const props = defineProps({
    student: { type: Object, required: true },
    wallet: { type: Object, required: true },
    transactions: { type: Array, default: () => [] },
    bills: { type: Array, default: () => [] },
    summary: { type: Object, default: () => ({ wallet_balance: 0, total_remaining_bills: 0 }) },
});

const { formatRupiah } = useFormat();
const activeSection = ref('wallet'); // 'wallet' | 'bills'

const transactionColumns = [
    { key: 'created_at', label: 'Waktu' },
    { key: 'type', label: 'Jenis' },
    { key: 'description', label: 'Keterangan' },
    { key: 'amount', label: 'Nominal' },
    { key: 'status', label: 'Status' },
];

const billColumns = [
    { key: 'title', label: 'Nama Tagihan' },
    { key: 'period', label: 'Periode' },
    { key: 'amount', label: 'Total' },
    { key: 'remaining_amount', label: 'Sisa' },
    { key: 'status', label: 'Status' },
    { key: 'due_date', label: 'Jatuh Tempo' },
];
</script>

<template>
    <Head title="Keuangan & Saldo Siswa" />

    <PortalShell variant="bottom-nav">
        <template #header>
            <PageHeader
                title="Keuangan & Dompet Digital"
                :description="`${student.name} &bull; ${student.class_name} (${student.tingkat})`"
            />
        </template>

        <div class="space-y-4">
            <!-- 1. KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <StatCard
                    title="Saldo Dompet Aktif"
                    :value="formatRupiah(wallet.balance)"
                    description="Uang saku digital untuk jajan kantin"
                />
                <StatCard
                    title="Batas Jajan Harian"
                    :value="wallet.daily_limit > 0 ? formatRupiah(wallet.daily_limit) : 'Bebas'"
                    description="Batas belanja maksimal per hari"
                />
                <StatCard
                    title="Tagihan Sekolah Berjalan"
                    :value="formatRupiah(summary.total_remaining_bills)"
                    description="Total SPP/Iuran belum lunas"
                />
            </div>

            <!-- Privacy & Child Protection Alert Note -->
            <div class="p-3.5 rounded-card bg-brand-50 border border-brand-200 text-xs text-brand-900 flex items-start gap-2.5">
                <span class="text-base select-none mt-0.5">ℹ️</span>
                <div class="space-y-0.5">
                    <p class="font-bold">Informasi Transparansi Siswa (Hanya-Baca)</p>
                    <p class="text-ink-muted leading-relaxed text-[11px]">
                        Halaman ini menampilkan catatan uang saku digital dan status iuran sekolah ananda. Pengaturan PIN dan pembayaran tagihan dikelola aman oleh orang tua melalui Portal Orang Tua atau Kasir Bendahara.
                    </p>
                </div>
            </div>

            <!-- 2. Section Selector Tabs -->
            <div class="flex items-center gap-2 border-b border-line pb-2">
                <AppButton
                    size="sm"
                    :variant="activeSection === 'wallet' ? 'primary' : 'secondary'"
                    @click="activeSection = 'wallet'"
                >
                    Mutasi Dompet Kantin ({{ transactions.length }})
                </AppButton>
                <AppButton
                    size="sm"
                    :variant="activeSection === 'bills' ? 'primary' : 'secondary'"
                    @click="activeSection = 'bills'"
                >
                    Tagihan SPP & Iuran ({{ bills.length }})
                </AppButton>
            </div>

            <!-- 3. Section Content -->
            <!-- Tab A: Wallet Transactions -->
            <div v-if="activeSection === 'wallet'" class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-ink-muted">
                        Riwayat Transaksi Uang Saku Digital
                    </h3>
                    <span class="text-xs font-mono text-brand-700 font-bold">
                        Kartu RFID: {{ wallet.rfid_card_id }}
                    </span>
                </div>

                <DataTable
                    :columns="transactionColumns"
                    :items="transactions"
                    empty-title="Belum Ada Transaksi Dompet"
                    empty-message="Belum ada riwayat top-up saldo atau transaksi belanja kantin."
                >
                    <template #cell(created_at)="{ item }">
                        <span class="text-xs text-ink-muted font-numeric">{{ item.created_at }}</span>
                    </template>
                    <template #cell(type)="{ item }">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-brand-50 text-brand-700">
                            {{ item.type }}
                        </span>
                    </template>
                    <template #cell(description)="{ item }">
                        <span class="font-medium text-ink">{{ item.description }}</span>
                    </template>
                    <template #cell(amount)="{ item }">
                        <span
                            :class="[
                                'font-numeric font-bold',
                                item.type === 'Setoran' ? 'text-status-success-fg' : 'text-ink'
                            ]"
                        >
                            {{ item.type === 'Setoran' ? '+' : '-' }}{{ formatRupiah(item.amount) }}
                        </span>
                    </template>
                    <template #cell(status)="{ item }">
                        <StatusBadge :status="item.status === 'Sukses' ? 'Selesai' : (item.status === 'Batal' ? 'Dibatalkan' : 'Menunggu')" />
                    </template>
                </DataTable>
            </div>

            <!-- Tab B: Bills List -->
            <div v-else-if="activeSection === 'bills'" class="space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-ink-muted">
                    Daftar Tagihan & Iuran Siswa
                </h3>

                <DataTable
                    :columns="billColumns"
                    :items="bills"
                    empty-title="Tidak Ada Tagihan"
                    empty-message="Alhamdulillah, tidak ada tagihan sekolah yang tertunda."
                >
                    <template #cell(title)="{ item }">
                        <span class="font-bold text-ink block">{{ item.title }}</span>
                    </template>
                    <template #cell(period)="{ item }">
                        <span class="text-xs text-ink-muted">{{ item.period }}</span>
                    </template>
                    <template #cell(amount)="{ item }">
                        <span class="font-numeric text-ink">{{ formatRupiah(item.amount) }}</span>
                    </template>
                    <template #cell(remaining_amount)="{ item }">
                        <span
                            :class="[
                                'font-numeric font-bold',
                                item.remaining_amount > 0 ? 'text-status-danger-fg' : 'text-status-success-fg'
                            ]"
                        >
                            {{ formatRupiah(item.remaining_amount) }}
                        </span>
                    </template>
                    <template #cell(status)="{ item }">
                        <StatusBadge :status="item.status === 'Lunas' ? 'Lunas' : (item.status === 'Sebagian' ? 'Sebagian' : 'Belum Lunas')" />
                    </template>
                    <template #cell(due_date)="{ item }">
                        <span class="text-xs text-ink-muted font-numeric">{{ item.due_date }}</span>
                    </template>
                </DataTable>
            </div>
        </div>
    </PortalShell>
</template>

