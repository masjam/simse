<script setup>
import { Head } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';
import KopSurat from '@/Components/domain/KopSurat.vue';

defineProps({
    slipGaji: { type: Object, default: null },
    payrollHistory: { type: Array, required: true },
});

const printSlip = () => {
    window.print();
};
</script>

<template>
    <Head title="Slip Gaji Saya" />
    <PortalShell variant="sidebar">
        <div class="space-y-6">
            <PageHeader
                title="Slip Gaji & Payroll Staf"
                subtitle="Transparansi honorarium, tunjangan kehadiran GPS, dan potongan resmi SD Muhammadiyah Al Mujahidin"
            />

            <div v-if="!slipGaji" class="bg-surface rounded-card border border-line p-8 shadow-card max-w-3xl mx-auto">
                <EmptyState
                    title="Belum Ada Slip Gaji"
                    description="Belum ada slip gaji yang diterbitkan untuk akun Anda pada periode ini. Silakan hubungi bagian keuangan/bendahara."
                />
            </div>

            <div v-else class="max-w-4xl mx-auto space-y-6">
                <!-- Header Kartu Gaji Utama -->
                <div class="bg-brand-700 text-white rounded-card p-6 sm:p-8 shadow-card flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
                    <div>
                        <p class="text-xs uppercase tracking-wider font-bold text-white/80">
                            Total Gaji Bersih (Take Home Pay)
                        </p>
                        <h2 class="text-3xl sm:text-4xl font-black mt-1 font-numeric">
                            <MoneyText :amount="slipGaji.totalSalary" />
                        </h2>
                        <p class="text-xs text-white/80 mt-1">
                            Periode: {{ slipGaji.month }} • Status: <strong class="uppercase text-white">{{ slipGaji.status }}</strong>
                        </p>
                    </div>
                    <AppButton
                        variant="secondary"
                        size="lg"
                        class="shadow-card"
                        @click="printSlip"
                    >
                        🖨️ Cetak / Simpan PDF
                    </AppButton>
                </div>

                <!-- Rincian Slip Gaji Format Resmi -->
                <AppCard class="space-y-6">
                    <KopSurat compact />

                    <div class="text-center pb-2">
                        <h3 class="text-sm font-bold uppercase underline text-ink">
                            SLIP GAJI TENAGA KEPENDIDIKAN
                        </h3>
                        <p class="text-xs text-ink-muted mt-0.5">Periode Pembayaran: {{ slipGaji.month }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs bg-surface-raised p-4 rounded-control border border-line">
                        <div class="space-y-1">
                            <div><span class="text-ink-muted">Nama Pegawai:</span> <strong class="text-ink ml-1">{{ slipGaji.name }}</strong></div>
                            <div><span class="text-ink-muted">NIP / ID Staf:</span> <span class="font-mono text-ink ml-1">{{ slipGaji.nip }}</span></div>
                        </div>
                        <div class="space-y-1">
                            <div><span class="text-ink-muted">Jabatan / Posisi:</span> <span class="text-ink ml-1">{{ slipGaji.jabatan }}</span></div>
                            <div><span class="text-ink-muted">Tanggal Payroll:</span> <span class="text-ink ml-1">{{ slipGaji.paidDate }}</span></div>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Komponen Penerimaan -->
                        <div class="space-y-2">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-status-success-fg pb-1 border-b border-line">
                                Penerimaan (Penghasilan)
                            </h4>
                            <div
                                v-for="(item, idx) in slipGaji.earnings"
                                :key="idx"
                                class="flex justify-between text-xs py-1 text-ink"
                            >
                                <span class="text-ink-muted">{{ item.label }}</span>
                                <span class="font-bold font-numeric"><MoneyText :amount="item.amount" /></span>
                            </div>
                        </div>

                        <!-- Komponen Potongan -->
                        <div class="space-y-2">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-status-danger-fg pb-1 border-b border-line">
                                Potongan Resmi
                            </h4>
                            <div v-if="slipGaji.deductions.length === 0" class="text-xs text-ink-muted py-1">
                                Tidak ada potongan pada periode ini.
                            </div>
                            <div
                                v-else
                                v-for="(item, idx) in slipGaji.deductions"
                                :key="idx"
                                class="flex justify-between text-xs py-1"
                            >
                                <span class="text-ink-muted">{{ item.label }}</span>
                                <span class="font-bold text-status-danger-fg font-numeric">
                                    - <MoneyText :amount="item.amount" />
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-line flex justify-between items-center bg-surface-raised p-4 rounded-control">
                        <span class="text-xs font-bold text-ink uppercase">Gaji Bersih Diterima</span>
                        <span class="text-lg font-black text-brand-700 font-numeric">
                            <MoneyText :amount="slipGaji.totalSalary" />
                        </span>
                    </div>
                </AppCard>

                <!-- Riwayat Payroll Masa Lalu -->
                <AppCard class="print:hidden space-y-4">
                    <h3 class="font-bold text-ink text-sm">Riwayat Payroll Staf</h3>
                    <div v-if="payrollHistory.length === 0" class="text-xs text-ink-muted text-center py-4">
                        Belum ada riwayat slip gaji sebelumnya.
                    </div>
                    <div v-else class="divide-y divide-line">
                        <div
                            v-for="h in payrollHistory"
                            :key="h.id"
                            class="py-3 flex items-center justify-between text-xs"
                        >
                            <div class="space-y-0.5">
                                <p class="font-bold text-ink">{{ h.month }}</p>
                                <p class="text-ink-muted">{{ h.date }}</p>
                            </div>
                            <div class="text-right space-y-1">
                                <p class="font-black text-ink font-numeric">
                                    <MoneyText :amount="h.takeHome" />
                                </p>
                                <StatusBadge :variant="h.status === 'paid' ? 'success' : 'warning'" size="sm">
                                    {{ h.status }}
                                </StatusBadge>
                            </div>
                        </div>
                    </div>
                </AppCard>
            </div>
        </div>
    </PortalShell>
</template>
