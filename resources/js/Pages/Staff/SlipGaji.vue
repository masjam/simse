<script setup>
import StaffLayout from '@/Layouts/StaffLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    slipGaji: { type: Object, default: null },
    payrollHistory: { type: Array, required: true },
});

const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
}).format(amount || 0);

const printSlip = () => {
    window.print();
};
</script>

<template>
    <Head title="Slip Gaji Saya" />

    <StaffLayout>
        <template #header-title>
            <h1 class="text-2xl font-black text-slate-800">Slip Gaji &amp; Payroll Staf</h1>
        </template>
        <template #header-subtitle>
            <p class="text-slate-500 font-medium">Transparansi honor, tunjangan kehadiran GPS, dan potongan resmi</p>
        </template>

        <div v-if="!slipGaji" class="bg-white border border-slate-200 rounded-3xl p-10 text-center text-sm text-slate-500 max-w-3xl mx-auto shadow-sm">
            Belum ada slip gaji yang digenerate untuk akun Anda. Silakan hubungi Bendahara Sekolah.
        </div>

        <div v-else class="max-w-4xl mx-auto space-y-6">
            <!-- Header Kartu Gaji Utama -->
            <div class="bg-gradient-to-br from-indigo-700 via-indigo-800 to-slate-900 rounded-3xl p-6 sm:p-8 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xl print:hidden">
                <div>
                    <p class="text-xs uppercase tracking-wider font-bold text-indigo-200">Gaji Bersih Diterima</p>
                    <h2 class="text-3xl sm:text-4xl font-black mt-1">{{ formatRupiah(slipGaji.totalSalary) }}</h2>
                    <p class="text-xs text-indigo-200 mt-1">Periode: {{ slipGaji.month }} &middot; Status: <strong class="uppercase">{{ slipGaji.status }}</strong></p>
                </div>
                <button
                    type="button"
                    class="px-5 py-2.5 bg-white text-indigo-900 hover:bg-indigo-50 font-extrabold text-xs rounded-xl shadow transition"
                    @click="printSlip"
                >
                    Cetak / Simpan PDF
                </button>
            </div>

            <!-- Rincian Slip Gaji Format Resmi -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
                <div class="text-center pb-6 border-b border-slate-100">
                    <p class="text-xs font-bold uppercase tracking-wider text-indigo-700">SD Muhammadiyah Al Mujahidin Wonosari</p>
                    <h3 class="text-xl font-black text-slate-900 mt-1">SLIP GAJI TENAGA KEPENDIDIKAN</h3>
                    <p class="text-xs text-slate-500">Periode Pembayaran: {{ slipGaji.month }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4 text-xs bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <div>
                        <span class="text-slate-400 block">Nama Pegawai:</span>
                        <strong class="text-slate-800 text-sm">{{ slipGaji.name }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">ID / NIP Staf:</span>
                        <strong class="text-slate-800 font-mono">{{ slipGaji.nip }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Jabatan / Posisi:</span>
                        <strong class="text-slate-800">{{ slipGaji.jabatan }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Tanggal Payroll:</span>
                        <strong class="text-slate-800">{{ slipGaji.paidDate }}</strong>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Komponen Penerimaan -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-emerald-700 pb-1 border-b border-emerald-100">
                            Penerimaan (Penghasilan)
                        </h4>
                        <div v-for="(item, idx) in slipGaji.earnings" :key="idx" class="flex justify-between text-xs py-1">
                            <span class="text-slate-600">{{ item.label }}</span>
                            <span class="font-bold text-slate-900">{{ formatRupiah(item.amount) }}</span>
                        </div>
                    </div>

                    <!-- Komponen Potongan -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-rose-700 pb-1 border-b border-rose-100">
                            Potongan Resmi
                        </h4>
                        <div v-if="slipGaji.deductions.length === 0" class="text-xs text-slate-400 py-1">
                            Tidak ada potongan pada periode ini.
                        </div>
                        <div v-else v-for="(item, idx) in slipGaji.deductions" :key="idx" class="flex justify-between text-xs py-1">
                            <span class="text-slate-600">{{ item.label }}</span>
                            <span class="font-bold text-rose-700">- {{ formatRupiah(item.amount) }}</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 flex justify-between items-center bg-slate-50 p-4 rounded-2xl">
                    <span class="text-sm font-black text-slate-800 uppercase">Gaji Bersih (Take Home Pay)</span>
                    <span class="text-xl font-black text-indigo-700">{{ formatRupiah(slipGaji.totalSalary) }}</span>
                </div>
            </div>

            <!-- Riwayat Payroll Masa Lalu -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm overflow-hidden print:hidden">
                <h3 class="font-extrabold text-slate-800 mb-4">Riwayat Payroll Staf</h3>
                <div v-if="payrollHistory.length === 0" class="text-xs text-slate-400 text-center py-4">
                    Belum ada riwayat slip gaji sebelumnya.
                </div>
                <div v-else class="divide-y divide-slate-100">
                    <div v-for="h in payrollHistory" :key="h.id" class="py-3 flex items-center justify-between text-xs">
                        <div>
                            <p class="font-bold text-slate-800">{{ h.month }}</p>
                            <p class="text-slate-400">{{ h.date }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-black text-slate-900">{{ formatRupiah(h.takeHome) }}</p>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase" :class="h.status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'">
                                {{ h.status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StaffLayout>
</template>
