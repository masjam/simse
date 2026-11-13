<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_kas: 'Rp 0',
            spp_terkumpul: 'Rp 0',
            tunggakan_spp: 'Rp 0',
            payroll_terbayar: 'Rp 0',
            pending_verifications: 0,
            draft_payrolls: 0,
            total_dompet_siswa: 'Rp 0',
        }),
    },
    recentTransactions: {
        type: Array,
        default: () => [],
    },
});

const trxColumns = [
    { key: 'title', label: 'Keterangan', sortable: false },
    { key: 'category', label: 'Tipe', sortable: false },
    { key: 'date', label: 'Tanggal', sortable: false },
    { key: 'amount', label: 'Nominal', sortable: false, class: 'text-right' },
];
</script>

<template>
    <Head title="Dashboard Keuangan" />
    <PortalShell variant="sidebar">
        <div class="space-y-6">
            <PageHeader
                title="Dashboard Keuangan"
                subtitle="Ringkasan arus kas, tagihan SPP, dompet siswa, dan penggajian SD Muhammadiyah Al Mujahidin"
            />

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <StatCard
                    title="Total Kas Aktif"
                    :value="stats.total_kas"
                    subtitle="Saldo Sekolah Berjalan"
                    icon-bg="success"
                >
                    <template #icon>💰</template>
                </StatCard>
                <StatCard
                    title="SPP Terkumpul"
                    :value="stats.spp_terkumpul"
                    subtitle="Bulan Berjalan"
                    icon-bg="brand"
                >
                    <template #icon>📑</template>
                </StatCard>
                <StatCard
                    title="Tunggakan SPP"
                    :value="stats.tunggakan_spp"
                    subtitle="Piutang Menunggu Bayar"
                    icon-bg="danger"
                >
                    <template #icon>⚠️</template>
                </StatCard>
                <StatCard
                    title="Payroll Terbayar"
                    :value="stats.payroll_terbayar"
                    subtitle="Gaji Guru & Staf"
                    icon-bg="warning"
                >
                    <template #icon>👥</template>
                </StatCard>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Quick Actions -->
                <div class="lg:col-span-1">
                    <AppCard>
                        <h3 class="text-base font-bold text-ink mb-4">Aksi Cepat Keuangan</h3>
                        <div class="space-y-3">
                            <Link
                                :href="route('bendahara.kasir')"
                                class="w-full flex items-center gap-3.5 p-3.5 rounded-control border border-line bg-surface hover:bg-surface-raised hover:border-brand-300 transition group"
                            >
                                <div class="w-10 h-10 bg-brand-50 text-brand-700 rounded-control flex items-center justify-center font-bold shrink-0">
                                    💳
                                </div>
                                <div class="text-left flex-1 min-w-0">
                                    <p class="font-bold text-ink text-sm">Buka Kasir & Verifikasi</p>
                                    <p class="text-xs text-ink-muted truncate">
                                        {{ stats.pending_verifications }} bukti bayar menunggu
                                    </p>
                                </div>
                            </Link>

                            <Link
                                :href="route('bendahara.spp')"
                                class="w-full flex items-center gap-3.5 p-3.5 rounded-control border border-line bg-surface hover:bg-surface-raised hover:border-brand-300 transition group"
                            >
                                <div class="w-10 h-10 bg-status-info-bg text-status-info-fg rounded-control flex items-center justify-center font-bold shrink-0">
                                    📋
                                </div>
                                <div class="text-left flex-1 min-w-0">
                                    <p class="font-bold text-ink text-sm">Kelola Tagihan SPP</p>
                                    <p class="text-xs text-ink-muted truncate">
                                        Terbitkan pos & tagihan semester/bulanan
                                    </p>
                                </div>
                            </Link>

                            <Link
                                :href="route('bendahara.dompet')"
                                class="w-full flex items-center gap-3.5 p-3.5 rounded-control border border-line bg-surface hover:bg-surface-raised hover:border-brand-300 transition group"
                            >
                                <div class="w-10 h-10 bg-accent-50 text-accent-700 rounded-control flex items-center justify-center font-bold shrink-0">
                                    👛
                                </div>
                                <div class="text-left flex-1 min-w-0">
                                    <p class="font-bold text-ink text-sm">Dompet Digital Siswa</p>
                                    <p class="text-xs text-ink-muted truncate">
                                        Saldo mengendap {{ stats.total_dompet_siswa }}
                                    </p>
                                </div>
                            </Link>

                            <Link
                                :href="route('bendahara.payroll')"
                                class="w-full flex items-center gap-3.5 p-3.5 rounded-control border border-line bg-surface hover:bg-surface-raised hover:border-brand-300 transition group"
                            >
                                <div class="w-10 h-10 bg-status-warning-bg text-status-warning-fg rounded-control flex items-center justify-center font-bold shrink-0">
                                    💼
                                </div>
                                <div class="text-left flex-1 min-w-0">
                                    <p class="font-bold text-ink text-sm">Proses Gaji Pegawai</p>
                                    <p class="text-xs text-ink-muted truncate">
                                        {{ stats.draft_payrolls }} draf payroll belum cair
                                    </p>
                                </div>
                            </Link>
                        </div>
                    </AppCard>
                </div>

                <!-- Recent Transactions -->
                <div class="lg:col-span-2">
                    <AppCard class="h-full flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-base font-bold text-ink">Transaksi Kas Terbaru</h3>
                                <p class="text-xs text-ink-muted">Aktivitas penerimaan kasir & transfer bank</p>
                            </div>
                            <Link :href="route('bendahara.kasir')">
                                <AppButton variant="secondary" size="sm">
                                    Buka Kasir
                                </AppButton>
                            </Link>
                        </div>

                        <div class="flex-1">
                            <DataTable
                                :columns="trxColumns"
                                :items="recentTransactions"
                                empty-title="Belum Ada Transaksi"
                                empty-message="Belum ada transaksi keuangan yang tercatat hari ini."
                            >
                                <template #cell(title)="{ item }">
                                    <div>
                                        <p class="font-bold text-ink text-xs">{{ item.title }}</p>
                                        <p class="text-[11px] text-ink-muted">{{ item.subtitle }}</p>
                                    </div>
                                </template>

                                <template #cell(category)="{ value }">
                                    <StatusBadge
                                        :variant="value === 'Pemasukan' ? 'success' : 'danger'"
                                        size="sm"
                                    >
                                        {{ value }}
                                    </StatusBadge>
                                </template>

                                <template #cell(date)="{ value }">
                                    <span class="text-xs text-ink-muted">{{ value }}</span>
                                </template>

                                <template #cell(amount)="{ item }">
                                    <span
                                        class="font-black text-xs font-numeric"
                                        :class="item.category === 'Pemasukan' ? 'text-status-success-fg' : 'text-status-danger-fg'"
                                    >
                                        <MoneyText :amount="item.amount" />
                                    </span>
                                </template>
                            </DataTable>
                        </div>
                    </AppCard>
                </div>
            </div>
        </div>
    </PortalShell>
</template>
