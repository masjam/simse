<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import GeneratePayrollModal from './Partials/GeneratePayrollModal.vue';
import PayrollComponentModal from './Partials/PayrollComponentModal.vue';
import PayrollSlipViewModal from './Partials/PayrollSlipViewModal.vue';

const props = defineProps({
    payrolls: { type: Array, default: () => [] },
    summary: {
        type: Object,
        default: () => ({
            draft_total: 0,
            pending_total: 0,
            paid_total: 0,
        }),
    },
    selectedMonth: { type: String, default: 'November' },
    selectedYear: { type: String, default: '2026' },
});

const { confirm } = useConfirm();
const toast = useToast();

const filterMonth = ref(props.selectedMonth);
const filterYear = ref(props.selectedYear);

const months = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

const showGenerateModal = ref(false);
const showComponentModal = ref(false);
const showSlipModal = ref(false);
const activePayroll = ref(null);

const applyFilter = () => {
    router.get(route('bendahara.payroll'), {
        month: filterMonth.value,
        year: filterYear.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const submitAllDrafts = async () => {
    const ok = await confirm({
        title: 'Ajukan Semua Draft Gaji',
        message: `Ajukan semua draf payroll periode ${filterMonth.value} ${filterYear.value} untuk persetujuan pimpinan?`,
        confirmLabel: 'Ya, Ajukan Persetujuan',
        variant: 'primary',
    });
    if (ok) {
        router.post(route('bendahara.payroll.submit-approval'), {
            month: filterMonth.value,
            year: filterYear.value,
        }, {
            preserveScroll: true,
            onSuccess: () => toast.success('Semua draft gaji berhasil diajukan untuk persetujuan.'),
        });
    }
};

const updateStatus = async (payrollId, status) => {
    const isPaid = status === 'Dibayar';
    if (isPaid) {
        const ok = await confirm({
            title: 'Cairkan Gaji Pegawai',
            message: 'Konfirmasi pencairan gaji pegawai ini? Status akan diperbarui menjadi Dibayar.',
            confirmLabel: 'Cairkan Sekarang',
            variant: 'primary',
        });
        if (!ok) return;
    }

    router.patch(route('bendahara.payroll.status', payrollId), { status }, {
        preserveScroll: true,
        onSuccess: () => toast.success(`Status payroll berhasil diperbarui ke: ${status}`),
    });
};

const openComponentModal = (payroll) => {
    activePayroll.value = payroll;
    showComponentModal.value = true;
};

const openSlipModal = (payroll) => {
    activePayroll.value = payroll;
    showSlipModal.value = true;
};

const payrollColumns = [
    { key: 'nama', label: 'Nama Pegawai' },
    { key: 'gapok', label: 'Gaji Pokok', class: 'text-right' },
    { key: 'tunjangan', label: 'Tunjangan', class: 'text-right' },
    { key: 'potongan', label: 'Potongan', class: 'text-right' },
    { key: 'total', label: 'Total Bersih', class: 'text-right' },
    { key: 'status', label: 'Status', class: 'text-center' },
];
</script>

<template>
    <Head title="Payroll & Gaji" />
    <PortalShell variant="sidebar">
        <div class="space-y-6">
            <PageHeader
                title="Payroll & Penggajian Pegawai"
                subtitle="Kalkulasi honor mengajar, tunjangan kehadiran, potongan, dan pencairan slip gaji SD Muhammadiyah Al Mujahidin"
            />

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <StatCard
                    title="Total Draft Gaji"
                    :value="summary.draft_total"
                    :subtitle="`Periode ${selectedMonth} ${selectedYear}`"
                    icon-bg="warning"
                >
                    <template #icon>📝</template>
                </StatCard>
                <StatCard
                    title="Menunggu Persetujuan"
                    :value="summary.pending_total"
                    subtitle="Siap Diverifikasi & Dicairkan"
                    icon-bg="brand"
                >
                    <template #icon>⏳</template>
                </StatCard>
                <StatCard
                    title="Selesai Dibayar"
                    :value="summary.paid_total"
                    :subtitle="`Bulan ${selectedMonth} ${selectedYear}`"
                    icon-bg="success"
                >
                    <template #icon>✅</template>
                </StatCard>
            </div>

            <!-- Filter & Action Section -->
            <div class="space-y-4">
                <div class="bg-surface rounded-card border border-line p-4 flex flex-col sm:flex-row justify-between items-center gap-3 shadow-card">
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <div class="w-36">
                            <AppSelect
                                v-model="filterMonth"
                                :options="months.map(m => ({ value: m, label: m }))"
                                @change="applyFilter"
                            />
                        </div>
                        <div class="w-24">
                            <AppInput
                                v-model="filterYear"
                                placeholder="2026"
                                @change="applyFilter"
                            />
                        </div>
                    </div>
                    <div class="flex gap-2 w-full sm:w-auto justify-end">
                        <AppButton
                            variant="secondary"
                            size="sm"
                            @click="showGenerateModal = true"
                        >
                            Generate Gaji Massal
                        </AppButton>
                        <AppButton
                            variant="primary"
                            size="sm"
                            @click="submitAllDrafts"
                        >
                            Ajukan Semua Draft
                        </AppButton>
                    </div>
                </div>

                <DataTable
                    :columns="payrollColumns"
                    :items="payrolls"
                    empty-title="Belum Ada Data Payroll"
                    :empty-message="`Belum ada draf payroll pada periode ${selectedMonth} ${selectedYear}. Klik 'Generate Gaji Massal'.`"
                >
                    <template #cell(nama)="{ item }">
                        <div>
                            <p class="font-bold text-ink text-xs">{{ item.nama }}</p>
                            <p class="text-[11px] text-ink-muted">NIP: {{ item.nip }}</p>
                        </div>
                    </template>

                    <template #cell(gapok)="{ value }">
                        <span class="text-xs text-ink font-numeric"><MoneyText :amount="value" /></span>
                    </template>

                    <template #cell(tunjangan)="{ value }">
                        <span class="text-xs text-status-success-fg font-numeric">+ <MoneyText :amount="value" /></span>
                    </template>

                    <template #cell(potongan)="{ value }">
                        <span class="text-xs text-status-danger-fg font-numeric">- <MoneyText :amount="value" /></span>
                    </template>

                    <template #cell(total)="{ value }">
                        <span class="font-black text-xs text-ink font-numeric">
                            <MoneyText :amount="value" />
                        </span>
                    </template>

                    <template #cell(status)="{ value }">
                        <div class="flex justify-center">
                            <StatusBadge
                                :variant="value === 'Dibayar' ? 'success' : (value === 'Draft' ? 'warning' : 'brand')"
                                size="sm"
                            >
                                {{ value }}
                            </StatusBadge>
                        </div>
                    </template>

                    <template #actions="{ item }">
                        <div class="flex items-center justify-end gap-1.5 flex-wrap">
                            <AppButton
                                v-if="item.status !== 'Dibayar'"
                                variant="secondary"
                                size="sm"
                                @click="openComponentModal(item)"
                            >
                                Komponen
                            </AppButton>
                            <AppButton
                                v-if="item.status === 'Draft'"
                                variant="outline"
                                size="sm"
                                @click="updateStatus(item.id, 'Menunggu Persetujuan')"
                            >
                                Ajukan
                            </AppButton>
                            <AppButton
                                v-if="item.status !== 'Dibayar'"
                                variant="primary"
                                size="sm"
                                @click="updateStatus(item.id, 'Dibayar')"
                            >
                                Cairkan
                            </AppButton>
                            <AppButton
                                variant="ghost"
                                size="sm"
                                @click="openSlipModal(item)"
                            >
                                Slip
                            </AppButton>
                        </div>
                    </template>
                </DataTable>
            </div>

            <!-- Modals -->
            <GeneratePayrollModal
                :show="showGenerateModal"
                :initial-month="selectedMonth"
                :initial-year="selectedYear"
                :months="months"
                @close="showGenerateModal = false"
                @generated="toast.success('Payroll massal berhasil dibuat!')"
            />

            <PayrollComponentModal
                :show="showComponentModal"
                :payroll="activePayroll"
                @close="showComponentModal = false"
                @updated="toast.success('Komponen gaji berhasil diperbarui!')"
            />

            <PayrollSlipViewModal
                :show="showSlipModal"
                :payroll="activePayroll"
                @close="showSlipModal = false"
            />
        </div>
    </PortalShell>
</template>
