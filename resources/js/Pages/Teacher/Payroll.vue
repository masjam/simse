<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import Modal from '@/Components/ui/Modal.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';
import KopSurat from '@/Components/domain/KopSurat.vue';

const props = defineProps({
    slipGaji: { type: Object, default: () => null },
    payrollHistory: { type: Array, default: () => [] },
});

const defaultSlip = {
    month: 'Oktober 2026',
    name: 'Siti Guru, S.Pd.I',
    nip: '198407152010012015',
    jabatan: 'Guru Pendidikan Agama Islam & Budi Pekerti',
    golongan: 'Penata Muda / III-A',
    status: 'paid',
    paidDate: '25 Oktober 2026',
    earnings: [
        { label: 'Gaji Pokok Pendidik', amount: 3500000 },
        { label: 'Tunjangan Fungsional Guru', amount: 850000 },
        { label: 'Insentif Wali Kelas & Pembiasaan Sholat', amount: 600000 },
        { label: 'Uang Makan & Kehadiran (22 Hari)', amount: 660000 },
    ],
    deductions: [
        { label: 'Iuran BPJS Ketenagakerjaan & Kesehatan', amount: 110000 },
        { label: 'Simpanan Pokok & Wajib Koperasi', amount: 50000 },
    ],
};

const slip = computed(() => props.slipGaji || defaultSlip);

const totalEarnings = computed(() => (slip.value.earnings || []).reduce((a, e) => a + Number(e.amount), 0));
const totalDeductions = computed(() => (slip.value.deductions || []).reduce((a, d) => a + Number(d.amount), 0));
const takeHome = computed(() => totalEarnings.value - totalDeductions.value);

const historyColumns = [
    { key: 'month', label: 'Periode Bulan', sortable: false },
    { key: 'date', label: 'Tanggal Pembayaran', sortable: false },
    { key: 'takeHome', label: 'Nominal Bersih', sortable: false },
    { key: 'status', label: 'Status', sortable: false, class: 'text-center' },
];

const showPrintSlip = ref(false);
const printDoc = () => window.print();
</script>

<template>
    <Head title="Slip Gaji & Payroll" />
    <PortalShell variant="sidebar">
        <div class="space-y-6">
            <PageHeader
                title="Slip Gaji & Penggajian Pendidik"
                subtitle="Transparansi honorarium dan penggajian SD Muhammadiyah Al Mujahidin"
            />

            <!-- Header Summary Card -->
            <div class="bg-brand-700 text-white rounded-card p-6 md:p-8 shadow-card relative overflow-hidden">
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-surface text-ink">
                                {{ slip.status === 'paid' ? 'LUNAS / TELAH DIBAYAR' : 'MENUNGGU PERSETUJUAN' }}
                            </span>
                            <span class="text-xs text-white/80">Periode: {{ slip.month }}</span>
                        </div>
                        <p class="text-xs text-white/80 font-semibold">Total Gaji Bersih (Take Home Pay):</p>
                        <h2 class="text-3xl md:text-4xl font-black mt-1">
                            <MoneyText :amount="takeHome" />
                        </h2>
                        <p class="text-xs text-white/70 mt-2 font-medium">
                            Ditransfer ke Rekening BSI Sekolah pada {{ slip.paidDate }}
                        </p>
                    </div>
                    <AppButton
                        variant="secondary"
                        size="lg"
                        class="shadow-card"
                        @click="showPrintSlip = true"
                    >
                        🖨️ Cetak Slip Gaji Resmi
                    </AppButton>
                </div>
            </div>

            <!-- Details: Earnings vs Deductions -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Earnings Card -->
                <AppCard>
                    <div class="flex items-center justify-between pb-3 border-b border-line mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-control bg-status-success-bg text-status-success-fg flex items-center justify-center font-bold">
                                ➕
                            </div>
                            <h3 class="font-bold text-ink text-sm">Rincian Penerimaan (Earnings)</h3>
                        </div>
                        <span class="text-xs font-black text-status-success-fg">
                            <MoneyText :amount="totalEarnings" />
                        </span>
                    </div>
                    <div class="space-y-3">
                        <div
                            v-for="(e, idx) in slip.earnings"
                            :key="idx"
                            class="flex justify-between items-center text-xs"
                        >
                            <span class="text-ink-muted font-medium">{{ e.label }}</span>
                            <span class="font-bold text-ink">
                                <MoneyText :amount="e.amount" />
                            </span>
                        </div>
                    </div>
                </AppCard>

                <!-- Deductions Card -->
                <AppCard>
                    <div class="flex items-center justify-between pb-3 border-b border-line mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-control bg-status-danger-bg text-status-danger-fg flex items-center justify-center font-bold">
                                ➖
                            </div>
                            <h3 class="font-bold text-ink text-sm">Rincian Potongan (Deductions)</h3>
                        </div>
                        <span class="text-xs font-black text-status-danger-fg">
                            - <MoneyText :amount="totalDeductions" />
                        </span>
                    </div>
                    <div class="space-y-3">
                        <div
                            v-for="(d, idx) in slip.deductions"
                            :key="idx"
                            class="flex justify-between items-center text-xs"
                        >
                            <span class="text-ink-muted font-medium">{{ d.label }}</span>
                            <span class="font-bold text-status-danger-fg">
                                - <MoneyText :amount="d.amount" />
                            </span>
                        </div>
                    </div>
                </AppCard>
            </div>

            <!-- Riwayat Penggajian Table -->
            <div class="space-y-3">
                <h3 class="font-bold text-ink text-sm px-1">Riwayat Penggajian Terakhir</h3>
                <DataTable
                    :columns="historyColumns"
                    :items="payrollHistory"
                    empty-title="Belum Ada Riwayat Penggajian"
                    empty-message="Catatan pembayaran gaji Anda akan tercantum di sini setelah diverifikasi bagian keuangan."
                >
                    <template #cell(takeHome)="{ value }">
                        <span class="font-black text-brand-700">
                            <MoneyText :amount="value" />
                        </span>
                    </template>
                    <template #cell(status)="{ value }">
                        <div class="flex justify-center">
                            <StatusBadge :variant="value === 'paid' ? 'success' : 'warning'" size="sm">
                                {{ value === 'paid' ? 'Lunas' : 'Menunggu' }}
                            </StatusBadge>
                        </div>
                    </template>
                    <template #actions>
                        <AppButton
                            variant="ghost"
                            size="sm"
                            @click="showPrintSlip = true"
                        >
                            Lihat Slip
                        </AppButton>
                    </template>
                </DataTable>
            </div>

            <!-- Formal Printable Slip Modal -->
            <Modal
                :show="showPrintSlip"
                max-width="2xl"
                @close="showPrintSlip = false"
            >
                <div class="p-6 md:p-8 space-y-6">
                    <div class="flex justify-between items-center pb-4 border-b border-line">
                        <h3 class="font-bold text-ink text-sm">Pratinjau Cetak Slip Gaji Resmi</h3>
                        <div class="flex gap-2">
                            <AppButton
                                variant="primary"
                                size="sm"
                                @click="printDoc"
                            >
                                🖨️ Cetak / PDF
                            </AppButton>
                            <AppButton
                                variant="secondary"
                                size="sm"
                                @click="showPrintSlip = false"
                            >
                                Tutup
                            </AppButton>
                        </div>
                    </div>

                    <!-- Formal Voucher Structure -->
                    <div class="border border-line p-6 rounded-card space-y-6 text-xs text-ink bg-surface shadow-card">
                        <KopSurat compact />

                        <div class="text-center pb-2">
                            <h3 class="text-xs font-bold uppercase underline">
                                SLIP PEMBAYARAN GAJI PENDIDIK & TENAGA KEPENDIDIKAN
                            </h3>
                            <p class="text-[11px] text-ink-muted mt-0.5">Periode: {{ slip.month }}</p>
                        </div>

                        <!-- Pendidik Details -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-surface-raised p-4 rounded-control border border-line text-xs">
                            <div class="space-y-1">
                                <div class="flex"><span class="w-24 text-ink-muted font-semibold">Nama</span>: <strong class="ml-1 text-ink">{{ slip.name }}</strong></div>
                                <div class="flex"><span class="w-24 text-ink-muted font-semibold">NIP</span>: <span class="ml-1 font-mono text-ink">{{ slip.nip }}</span></div>
                            </div>
                            <div class="space-y-1">
                                <div class="flex"><span class="w-24 text-ink-muted font-semibold">Jabatan</span>: <span class="ml-1 text-ink">{{ slip.jabatan }}</span></div>
                                <div class="flex"><span class="w-24 text-ink-muted font-semibold">Golongan</span>: <span class="ml-1 text-ink">{{ slip.golongan }}</span></div>
                            </div>
                        </div>

                        <!-- Rincian -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-bold border-b border-line pb-1 text-ink mb-2">A. PENERIMAAN</h4>
                                <div class="space-y-1.5">
                                    <div
                                        v-for="(e, idx) in slip.earnings"
                                        :key="idx"
                                        class="flex justify-between"
                                    >
                                        <span class="text-ink-muted">{{ e.label }}</span>
                                        <span class="font-bold text-ink"><MoneyText :amount="e.amount" /></span>
                                    </div>
                                </div>
                                <div class="border-t border-line pt-2 mt-3 flex justify-between font-black text-status-success-fg">
                                    <span>Total Penerimaan:</span>
                                    <span><MoneyText :amount="totalEarnings" /></span>
                                </div>
                            </div>

                            <div>
                                <h4 class="font-bold border-b border-line pb-1 text-ink mb-2">B. POTONGAN</h4>
                                <div class="space-y-1.5">
                                    <div
                                        v-for="(d, idx) in slip.deductions"
                                        :key="idx"
                                        class="flex justify-between"
                                    >
                                        <span class="text-ink-muted">{{ d.label }}</span>
                                        <span class="font-bold text-status-danger-fg">- <MoneyText :amount="d.amount" /></span>
                                    </div>
                                </div>
                                <div class="border-t border-line pt-2 mt-3 flex justify-between font-black text-status-danger-fg">
                                    <span>Total Potongan:</span>
                                    <span><MoneyText :amount="totalDeductions" /></span>
                                </div>
                            </div>
                        </div>

                        <!-- Net Take Home -->
                        <div class="p-4 bg-brand-50 rounded-control border border-brand-200 flex justify-between items-center text-sm">
                            <span class="font-bold text-brand-900">GAJI BERSIH DITERIMA (TAKE HOME PAY):</span>
                            <span class="font-black text-brand-900 text-lg">
                                <MoneyText :amount="takeHome" />
                            </span>
                        </div>

                        <!-- Signatures & QR -->
                        <div class="grid grid-cols-3 gap-4 pt-6 text-center text-xs">
                            <div>
                                <p class="text-ink-muted">Penerima,</p>
                                <div class="h-12"></div>
                                <p class="font-bold text-ink">{{ slip.name }}</p>
                            </div>
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-surface-raised border border-line rounded-control flex items-center justify-center font-mono text-[9px] text-center p-1 font-bold text-ink-muted">
                                    [QR VERIFIKASI SDAM]
                                </div>
                                <span class="text-[9px] text-ink-muted mt-1">Otentikasi Digital</span>
                            </div>
                            <div>
                                <p class="text-ink-muted">Bendahara Sekolah,</p>
                                <div class="h-12"></div>
                                <p class="font-bold text-ink">Ahmad Syukron, S.E.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </Modal>
        </div>
    </PortalShell>
</template>
