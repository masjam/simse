<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';

const props = defineProps({
    filters: { type: Object, required: true },
    summary: { type: Object, required: true },
    stands: { type: Array, required: true },
    recentOrders: { type: Array, required: true },
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

const applyFilter = () => {
    router.get(route('bendahara.kantin'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, { preserveState: true });
};

const standColumns = [
    { key: 'name', label: 'Nama Stand' },
    { key: 'owner_name', label: 'Pengelola' },
    { key: 'is_open', label: 'Status Operasional', class: 'text-center' },
    { key: 'total_orders', label: 'Pesanan', class: 'text-right' },
    { key: 'completed_orders', label: 'Selesai', class: 'text-right' },
    { key: 'total_revenue', label: 'Omzet Periode', class: 'text-right' },
    { key: 'balance', label: 'Saldo Stand', class: 'text-right' },
];

const orderColumns = [
    { key: 'qr_code_pickup', label: 'Kode Pesanan' },
    { key: 'student_name', label: 'Siswa' },
    { key: 'stand_name', label: 'Stand' },
    { key: 'items_summary', label: 'Rincian Menu' },
    { key: 'status', label: 'Status', class: 'text-center' },
    { key: 'total_price', label: 'Total', class: 'text-right' },
];
</script>

<template>
    <Head title="Laporan Penjualan e-Kantin" />
    <PortalShell variant="sidebar">
        <div class="space-y-6">
            <PageHeader
                title="Laporan Penjualan e-Kantin"
                subtitle="Rekapitulasi transaksi, pembagian hasil, dan omzet per stan kantin SD Muhammadiyah Al Mujahidin"
            />

            <!-- Filter Bar -->
            <div class="bg-surface rounded-card border border-line p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 shadow-card">
                <p class="text-xs text-ink-muted">Pilih rentang tanggal transaksi untuk memfilter laporan penjualan</p>
                <form class="flex flex-wrap items-center gap-2 w-full sm:w-auto" @submit.prevent="applyFilter">
                    <div class="w-36">
                        <AppInput v-model="startDate" type="date" />
                    </div>
                    <span class="text-xs text-ink-muted font-bold">s/d</span>
                    <div class="w-36">
                        <AppInput v-model="endDate" type="date" />
                    </div>
                    <AppButton type="submit" variant="primary" size="sm">
                        Filter
                    </AppButton>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <StatCard
                    title="Jumlah Stand"
                    :value="summary.total_stands"
                    subtitle="Stan Terdaftar"
                    icon-bg="brand"
                >
                    <template #icon>🏪</template>
                </StatCard>
                <StatCard
                    title="Total Pesanan"
                    :value="summary.total_orders"
                    subtitle="Transaksi Selesai"
                    icon-bg="accent"
                >
                    <template #icon>🧾</template>
                </StatCard>
                <StatCard
                    title="Total Omzet"
                    :value="summary.total_revenue"
                    subtitle="Pendapatan Kotor"
                    icon-bg="success"
                >
                    <template #icon>💰</template>
                </StatCard>
                <StatCard
                    title="Total Saldo Stand"
                    :value="summary.total_stand_balance"
                    subtitle="Kewajiban Sekolah"
                    icon-bg="warning"
                >
                    <template #icon>🏦</template>
                </StatCard>
            </div>

            <!-- Stand Recapitulation -->
            <div class="space-y-3">
                <h3 class="text-base font-bold text-ink px-1">Rekapitulasi Omzet Per Stand Kantin</h3>
                <DataTable
                    :columns="standColumns"
                    :items="stands"
                    empty-title="Belum Ada Data Stand"
                    empty-message="Belum ada stan kantin yang terdaftar di sistem e-Kantin."
                >
                    <template #cell(name)="{ item }">
                        <strong class="text-xs font-bold text-ink">{{ item.name }}</strong>
                    </template>

                    <template #cell(owner_name)="{ value }">
                        <span class="text-xs text-ink-muted">{{ value }}</span>
                    </template>

                    <template #cell(is_open)="{ value }">
                        <div class="flex justify-center">
                            <StatusBadge :variant="value ? 'success' : 'neutral'" size="sm">
                                {{ value ? 'Buka' : 'Tutup' }}
                            </StatusBadge>
                        </div>
                    </template>

                    <template #cell(total_orders)="{ value }">
                        <span class="text-xs font-semibold text-ink font-numeric">{{ value }}</span>
                    </template>

                    <template #cell(completed_orders)="{ value }">
                        <span class="text-xs font-semibold text-status-success-fg font-numeric">{{ value }}</span>
                    </template>

                    <template #cell(total_revenue)="{ value }">
                        <span class="text-xs font-black text-brand-700 font-numeric">
                            <MoneyText :amount="value" />
                        </span>
                    </template>

                    <template #cell(balance)="{ value }">
                        <span class="text-xs font-bold text-ink font-numeric">
                            <MoneyText :amount="value" />
                        </span>
                    </template>
                </DataTable>
            </div>

            <!-- Recent Orders -->
            <div class="space-y-3">
                <h3 class="text-base font-bold text-ink px-1">Transaksi Kantin Terbaru</h3>
                <DataTable
                    :columns="orderColumns"
                    :items="recentOrders"
                    empty-title="Belum Ada Transaksi"
                    empty-message="Belum ada transaksi pembelian pada rentang tanggal yang dipilih."
                >
                    <template #cell(qr_code_pickup)="{ value }">
                        <span class="font-mono text-xs font-bold text-brand-700">{{ value }}</span>
                    </template>

                    <template #cell(student_name)="{ value }">
                        <span class="text-xs font-bold text-ink">{{ value }}</span>
                    </template>

                    <template #cell(stand_name)="{ value }">
                        <span class="text-xs text-ink-muted">{{ value }}</span>
                    </template>

                    <template #cell(items_summary)="{ value }">
                        <span class="text-xs text-ink-muted truncate max-w-xs block">{{ value }}</span>
                    </template>

                    <template #cell(status)="{ value }">
                        <div class="flex justify-center">
                            <StatusBadge :variant="value === 'Selesai' ? 'success' : 'warning'" size="sm">
                                {{ value }}
                            </StatusBadge>
                        </div>
                    </template>

                    <template #cell(total_price)="{ value }">
                        <span class="text-xs font-black text-ink font-numeric">
                            <MoneyText :amount="value" />
                        </span>
                    </template>
                </DataTable>
            </div>
        </div>
    </PortalShell>
</template>
