<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import SearchInput from '@/Components/ui/SearchInput.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import BillTypeModal from './Partials/BillTypeModal.vue';
import GenerateBillModal from './Partials/GenerateBillModal.vue';

const props = defineProps({
    billTypes: { type: Array, default: () => [] },
    bills: { type: Array, default: () => [] },
    arrears: { type: Array, default: () => [] },
    classes: { type: Array, default: () => [] },
});

const { confirm } = useConfirm();
const toast = useToast();

const activeTab = ref('tagihan');
const billStatusFilter = ref('Semua');
const billPeriodFilter = ref('Semua');
const billSearch = ref('');

const showBillTypeModal = ref(false);
const editingBillType = ref(null);
const showGenerateModal = ref(false);
const selectedGenerateBillTypeId = ref(null);

const filteredBills = computed(() => {
    const q = billSearch.value.trim().toLowerCase();
    return props.bills.filter((b) => {
        const matchStatus = billStatusFilter.value === 'Semua' || b.status === billStatusFilter.value;
        const matchPeriod = billPeriodFilter.value === 'Semua' || b.period === billPeriodFilter.value;
        const matchQuery = !q ||
            b.student_name.toLowerCase().includes(q) ||
            b.nisn.toLowerCase().includes(q) ||
            b.bill_name.toLowerCase().includes(q) ||
            b.class.toLowerCase().includes(q) ||
            (b.month && b.month.toLowerCase().includes(q));
        return matchStatus && matchPeriod && matchQuery;
    });
});

const openCreateBillType = () => {
    editingBillType.value = null;
    showBillTypeModal.value = true;
};

const openEditBillType = (item) => {
    editingBillType.value = item;
    showBillTypeModal.value = true;
};

const deleteBillType = async (id) => {
    const ok = await confirm({
        title: 'Hapus Pos Tagihan Master',
        message: 'Yakin menghapus pos tagihan master ini? Semua tagihan siswa yang terkait akan ikut terhapus.',
        confirmLabel: 'Ya, Hapus',
        variant: 'danger',
    });
    if (ok) {
        router.delete(route('bendahara.spp.bill-types.destroy', id), {
            preserveScroll: true,
            onSuccess: () => toast.success('Pos tagihan berhasil dihapus.'),
        });
    }
};

const openGenerateModal = (billTypeId = null) => {
    selectedGenerateBillTypeId.value = billTypeId;
    showGenerateModal.value = true;
};

const deleteBill = async (id) => {
    const ok = await confirm({
        title: 'Hapus Tagihan Siswa',
        message: 'Yakin menghapus catatan tagihan siswa ini?',
        confirmLabel: 'Ya, Hapus',
        variant: 'danger',
    });
    if (ok) {
        router.delete(route('bendahara.spp.bills.destroy', id), {
            preserveScroll: true,
            onSuccess: () => toast.success('Tagihan siswa berhasil dihapus.'),
        });
    }
};

const sendReminder = (studentId = null) => {
    router.post(route('bendahara.spp.remind'), { student_id: studentId }, {
        preserveScroll: true,
        onSuccess: () => toast.success('Pengingat tagihan berhasil dikirim via WhatsApp.'),
    });
};

const billTypeColumns = [
    { key: 'name', label: 'Nama Pos Tagihan' },
    { key: 'amount', label: 'Nominal Baku' },
    { key: 'period', label: 'Periode' },
    { key: 'bills_count', label: 'Tagihan Terbit' },
];

const billColumns = [
    { key: 'student_name', label: 'Siswa' },
    { key: 'bill_name', label: 'Tagihan' },
    { key: 'period', label: 'Periode' },
    { key: 'amount_due', label: 'Total Tagihan', class: 'text-right' },
    { key: 'remaining', label: 'Sisa Bayar', class: 'text-right' },
    { key: 'status', label: 'Status', class: 'text-center' },
];

const arrearsColumns = [
    { key: 'nama', label: 'Siswa' },
    { key: 'kelas', label: 'Kelas' },
    { key: 'bulan', label: 'Rincian Tunggakan' },
    { key: 'tunggakan', label: 'Total Tunggakan', class: 'text-right' },
];
</script>

<template>
    <Head title="Manajemen SPP" />
    <PortalShell variant="sidebar">
        <div class="space-y-6">
            <PageHeader
                title="Manajemen SPP & Tagihan"
                subtitle="Pengaturan pos biaya, distribusi tagihan massal, dan laporan tunggakan SD Muhammadiyah Al Mujahidin"
            />

            <!-- Tabs Navigation -->
            <div class="flex flex-wrap gap-2 border-b border-line pb-2">
                <AppButton
                    size="sm"
                    :variant="activeTab === 'tagihan' ? 'primary' : 'secondary'"
                    @click="activeTab = 'tagihan'"
                >
                    📁 Pos Tagihan Master
                </AppButton>
                <AppButton
                    size="sm"
                    :variant="activeTab === 'daftar' ? 'primary' : 'secondary'"
                    @click="activeTab = 'daftar'"
                >
                    📋 Daftar Tagihan Siswa ({{ bills.length }})
                </AppButton>
                <AppButton
                    size="sm"
                    :variant="activeTab === 'tunggakan' ? 'danger' : 'secondary'"
                    @click="activeTab = 'tunggakan'"
                >
                    ⚠️ Laporan Tunggakan ({{ arrears.length }})
                </AppButton>
            </div>

            <!-- TAB 1: POS TAGIHAN MASTER -->
            <div v-if="activeTab === 'tagihan'" class="space-y-4">
                <div class="flex justify-between items-center gap-3">
                    <p class="text-xs text-ink-muted">Kelola jenis biaya rutin dan insidental siswa</p>
                    <div class="flex gap-2">
                        <AppButton
                            variant="secondary"
                            size="sm"
                            :disabled="billTypes.length === 0"
                            @click="openGenerateModal()"
                        >
                            Distribusi Tagihan Massal
                        </AppButton>
                        <AppButton
                            variant="primary"
                            size="sm"
                            @click="openCreateBillType"
                        >
                            + Buat Pos Tagihan
                        </AppButton>
                    </div>
                </div>

                <DataTable
                    :columns="billTypeColumns"
                    :items="billTypes"
                    empty-title="Belum Ada Pos Tagihan"
                    empty-message="Silakan buat pos tagihan master pertama untuk memulai penagihan."
                >
                    <template #cell(name)="{ item }">
                        <strong class="font-bold text-ink text-xs">{{ item.name }}</strong>
                    </template>

                    <template #cell(amount)="{ value }">
                        <span class="font-bold text-xs text-ink font-numeric">
                            <MoneyText :amount="value" />
                        </span>
                    </template>

                    <template #cell(period)="{ value }">
                        <StatusBadge
                            :variant="value === 'Bulanan' ? 'success' : (value === 'Semester' ? 'brand' : 'neutral')"
                            size="sm"
                        >
                            {{ value }}
                        </StatusBadge>
                    </template>

                    <template #cell(bills_count)="{ value }">
                        <span class="text-xs text-ink-muted font-medium">{{ value }} Siswa</span>
                    </template>

                    <template #actions="{ item }">
                        <div class="flex items-center justify-end gap-1.5">
                            <AppButton
                                variant="outline"
                                size="sm"
                                @click="openGenerateModal(item.id)"
                            >
                                Terbitkan
                            </AppButton>
                            <AppButton
                                variant="secondary"
                                size="sm"
                                @click="openEditBillType(item)"
                            >
                                Edit
                            </AppButton>
                            <AppButton
                                variant="ghost"
                                size="sm"
                                class="text-status-danger-fg"
                                @click="deleteBillType(item.id)"
                            >
                                Hapus
                            </AppButton>
                        </div>
                    </template>
                </DataTable>
            </div>

            <!-- TAB 2: DAFTAR TAGIHAN SISWA -->
            <div v-if="activeTab === 'daftar'" class="space-y-4">
                <div class="bg-surface rounded-card border border-line p-4 flex flex-col md:flex-row justify-between items-center gap-3 shadow-card">
                    <div class="flex flex-wrap gap-2 w-full md:w-auto flex-1">
                        <div class="w-full sm:w-64">
                            <SearchInput
                                v-model="billSearch"
                                placeholder="Cari siswa, kelas, tagihan..."
                            />
                        </div>
                        <div class="w-36">
                            <AppSelect
                                v-model="billStatusFilter"
                                :options="[
                                    { value: 'Semua', label: 'Semua Status' },
                                    { value: 'Belum Lunas', label: 'Belum Lunas' },
                                    { value: 'Angsuran', label: 'Angsuran' },
                                    { value: 'Lunas', label: 'Lunas' },
                                ]"
                            />
                        </div>
                        <div class="w-36">
                            <AppSelect
                                v-model="billPeriodFilter"
                                :options="[
                                    { value: 'Semua', label: 'Semua Periode' },
                                    { value: 'Bulanan', label: 'Bulanan' },
                                    { value: 'Semester', label: 'Semester' },
                                    { value: 'Tahunan', label: 'Tahunan' },
                                    { value: 'Sekali Bayar', label: 'Sekali Bayar' },
                                ]"
                            />
                        </div>
                    </div>
                    <AppButton
                        variant="primary"
                        size="sm"
                        :disabled="billTypes.length === 0"
                        @click="openGenerateModal()"
                    >
                        + Terbitkan Tagihan Massal
                    </AppButton>
                </div>

                <DataTable
                    :columns="billColumns"
                    :items="filteredBills"
                    empty-title="Belum Ada Tagihan Siswa"
                    empty-message="Tidak ada catatan tagihan siswa yang sesuai dengan filter pencarian."
                >
                    <template #cell(student_name)="{ item }">
                        <div>
                            <p class="font-bold text-ink text-xs">{{ item.student_name }}</p>
                            <p class="text-[11px] text-ink-muted">{{ item.class }} • NISN: {{ item.nisn }}</p>
                        </div>
                    </template>

                    <template #cell(bill_name)="{ value }">
                        <span class="font-semibold text-ink text-xs">{{ value }}</span>
                    </template>

                    <template #cell(period)="{ item }">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <StatusBadge
                                :variant="item.period === 'Bulanan' ? 'success' : (item.period === 'Semester' ? 'brand' : 'neutral')"
                                size="sm"
                            >
                                {{ item.period }}
                            </StatusBadge>
                            <span class="text-xs text-ink-muted">
                                {{ [item.month, item.year].filter(Boolean).join(' ') || '-' }}
                            </span>
                        </div>
                    </template>

                    <template #cell(amount_due)="{ value }">
                        <span class="text-xs font-semibold text-ink font-numeric">
                            <MoneyText :amount="value" />
                        </span>
                    </template>

                    <template #cell(remaining)="{ value }">
                        <span class="text-xs font-black text-ink font-numeric">
                            <MoneyText :amount="value" />
                        </span>
                    </template>

                    <template #cell(status)="{ value }">
                        <div class="flex justify-center">
                            <StatusBadge
                                :variant="value === 'Lunas' ? 'success' : (value === 'Angsuran' ? 'warning' : 'danger')"
                                size="sm"
                            >
                                {{ value }}
                            </StatusBadge>
                        </div>
                    </template>

                    <template #actions="{ item }">
                        <div class="flex items-center justify-end gap-1.5">
                            <Link
                                v-if="item.status !== 'Lunas'"
                                :href="route('bendahara.kasir', { student_id: item.student_id })"
                            >
                                <AppButton variant="primary" size="sm">
                                    Bayar
                                </AppButton>
                            </Link>
                            <AppButton
                                v-if="item.paid_amount === 0"
                                variant="ghost"
                                size="sm"
                                class="text-status-danger-fg"
                                @click="deleteBill(item.id)"
                            >
                                Hapus
                            </AppButton>
                        </div>
                    </template>
                </DataTable>
            </div>

            <!-- TAB 3: TUNGGAKAN -->
            <div v-if="activeTab === 'tunggakan'" class="space-y-4">
                <div class="bg-status-danger-bg/40 border border-status-danger-line rounded-card p-4 flex flex-col sm:flex-row justify-between items-center gap-3">
                    <div>
                        <h3 class="font-bold text-ink text-sm">Laporan Tunggakan Siswa Belum Lunas</h3>
                        <p class="text-xs text-ink-muted">Daftar siswa dengan tunggakan pembayaran berjalan</p>
                    </div>
                    <AppButton
                        variant="danger"
                        size="sm"
                        :disabled="arrears.length === 0"
                        @click="sendReminder()"
                    >
                        📲 Kirim Pengingat WA Massal
                    </AppButton>
                </div>

                <DataTable
                    :columns="arrearsColumns"
                    :items="arrears"
                    empty-title="Tidak Ada Tunggakan"
                    empty-message="Alhamdulillah, seluruh siswa telah melunasi kewajiban tagihannya."
                >
                    <template #cell(nama)="{ item }">
                        <div>
                            <p class="font-bold text-ink text-xs">{{ item.nama }}</p>
                            <p class="text-[11px] text-ink-muted">NISN: {{ item.nisn }}</p>
                        </div>
                    </template>

                    <template #cell(kelas)="{ value }">
                        <span class="font-semibold text-xs text-ink">{{ value }}</span>
                    </template>

                    <template #cell(bulan)="{ value }">
                        <span class="text-xs text-status-danger-fg font-medium">{{ value }}</span>
                    </template>

                    <template #cell(tunggakan)="{ value }">
                        <span class="font-black text-xs text-status-danger-fg font-numeric">
                            <MoneyText :amount="value" />
                        </span>
                    </template>

                    <template #actions="{ item }">
                        <div class="flex items-center justify-end gap-1.5">
                            <AppButton
                                variant="secondary"
                                size="sm"
                                @click="sendReminder(item.student_id)"
                            >
                                Ingatkan WA
                            </AppButton>
                            <Link :href="route('bendahara.kasir', { student_id: item.student_id })">
                                <AppButton variant="primary" size="sm">
                                    Bayar Sekarang
                                </AppButton>
                            </Link>
                        </div>
                    </template>
                </DataTable>
            </div>

            <!-- Modals -->
            <BillTypeModal
                :show="showBillTypeModal"
                :editing-item="editingBillType"
                @close="showBillTypeModal = false"
                @saved="toast.success('Pos tagihan master berhasil disimpan.')"
            />

            <GenerateBillModal
                :show="showGenerateModal"
                :bill-types="billTypes"
                :classes="classes"
                :initial-bill-type-id="selectedGenerateBillTypeId"
                @close="showGenerateModal = false"
                @generated="() => { activeTab = 'daftar'; toast.success('Tagihan siswa massal berhasil diterbitkan.'); }"
            />
        </div>
    </PortalShell>
</template>
