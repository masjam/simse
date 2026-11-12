<script setup>
import TeacherLayout from '@/Layouts/TeacherLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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

const formatRupiah = (n) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);

// Modal Print
const showPrintSlip = ref(false);
const printDoc = () => window.print();
</script>

<template>
    <Head title="Slip Gaji & Payroll" />
    <TeacherLayout>
        <template #header-title><h1 class="text-lg font-bold text-slate-800">Slip Gaji & Penggajian Pendidik</h1></template>
        <template #header-subtitle><p class="text-xs text-slate-500">Transparansi honorarium dan penggajian SD Muhammadiyah Al Mujahidin</p></template>

        <div class="space-y-6">
            <!-- Header Summary Card -->
            <div class="bg-gradient-to-r from-emerald-700 via-teal-700 to-indigo-800 rounded-2xl p-6 md:p-8 text-white shadow-xl shadow-emerald-700/15 relative overflow-hidden">
                <div class="absolute -right-12 -top-12 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-400 text-slate-900">
                                {{ slip.status === 'paid' ? 'LUNAS / TELAH DIBAYAR' : 'MENUNGGU PERSETUJUAN' }}
                            </span>
                            <span class="text-xs text-emerald-200">Bulan: {{ slip.month }}</span>
                        </div>
                        <p class="text-xs text-emerald-100 font-semibold">Total Gaji Bersih (Take Home Pay):</p>
                        <h2 class="text-3xl md:text-4xl font-black mt-1">{{ formatRupiah(takeHome) }}</h2>
                        <p class="text-xs text-emerald-200 mt-2 font-medium">Ditransfer ke Rekening BSI Sekolah pada {{ slip.paidDate }}</p>
                    </div>
                    <button @click="showPrintSlip = true" class="px-6 py-3 bg-white text-emerald-800 font-black text-xs rounded-xl shadow-lg hover:bg-emerald-50 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Cetak Slip Gaji Resmi
                    </button>
                </div>
            </div>

            <!-- Details: Earnings vs Deductions -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Earnings Card -->
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">➕</div>
                            <h3 class="font-bold text-slate-800 text-sm">Rincian Penerimaan (Earnings)</h3>
                        </div>
                        <span class="text-xs font-black text-emerald-600">{{ formatRupiah(totalEarnings) }}</span>
                    </div>
                    <div class="space-y-3">
                        <div v-for="(e, idx) in slip.earnings" :key="idx" class="flex justify-between items-center text-xs">
                            <span class="text-slate-600 font-medium">{{ e.label }}</span>
                            <span class="font-bold text-slate-800">{{ formatRupiah(e.amount) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Deductions Card -->
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold">➖</div>
                            <h3 class="font-bold text-slate-800 text-sm">Rincian Potongan (Deductions)</h3>
                        </div>
                        <span class="text-xs font-black text-rose-600">{{ formatRupiah(totalDeductions) }}</span>
                    </div>
                    <div class="space-y-3">
                        <div v-for="(d, idx) in slip.deductions" :key="idx" class="flex justify-between items-center text-xs">
                            <span class="text-slate-600 font-medium">{{ d.label }}</span>
                            <span class="font-bold text-rose-600">- {{ formatRupiah(d.amount) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Riwayat Penggajian Table -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h3 class="font-bold text-slate-800 text-sm">Riwayat Penggajian Terakhir</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="p-4">Periode Bulan</th>
                                <th class="p-4">Tanggal Pembayaran</th>
                                <th class="p-4">Nominal Gaji Bersih</th>
                                <th class="p-4 text-center">Status</th>
                                <th class="p-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="h in payrollHistory" :key="h.id" class="hover:bg-slate-50/50">
                                <td class="p-4 font-bold text-slate-800">{{ h.month }}</td>
                                <td class="p-4 text-slate-500">{{ h.date }}</td>
                                <td class="p-4 font-black text-emerald-700">{{ formatRupiah(h.takeHome) }}</td>
                                <td class="p-4 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        {{ h.status === 'paid' ? 'Lunas' : 'Menunggu' }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <button @click="showPrintSlip = true" class="text-emerald-600 font-bold hover:underline">
                                        Lihat Slip
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Formal Printable Slip Modal -->
            <Teleport to="body">
                <div v-if="showPrintSlip" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6 md:p-8 max-h-[92vh] overflow-y-auto space-y-6">
                        <div class="flex justify-between items-center pb-4 border-b border-slate-200">
                            <h3 class="font-bold text-slate-800 text-sm">Pratinjau Cetak Slip Gaji Resmi</h3>
                            <div class="flex gap-2">
                                <button @click="printDoc" class="px-4 py-2 bg-emerald-600 text-white font-bold text-xs rounded-lg hover:bg-emerald-700 transition">
                                    🖨️ Cetak / PDF
                                </button>
                                <button @click="showPrintSlip = false" class="px-3 py-2 bg-slate-100 text-slate-600 font-bold text-xs rounded-lg">
                                    Tutup
                                </button>
                            </div>
                        </div>

                        <!-- Formal Voucher Structure -->
                        <div class="border border-slate-300 p-8 rounded-xl space-y-6 text-xs text-slate-800 font-sans bg-white">
                            <div class="border-b-2 border-double border-slate-800 pb-3 text-center">
                                <h4 class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">MAJELIS DIKDASMEN PDM GUNUNGKIDUL</h4>
                                <h2 class="text-base font-black uppercase text-slate-900 tracking-wide mt-0.5">SD MUHAMMADIYAH AL MUJAHIDIN WONOSARI</h2>
                                <h3 class="text-xs font-bold uppercase underline mt-2">SLIP PEMBAYARAN GAJI PENDIDIK & TENAGA KEPENDIDIKAN</h3>
                                <p class="text-[11px] text-slate-500 mt-0.5">Periode: {{ slip.month }}</p>
                            </div>

                            <!-- Pendidik Details -->
                            <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded border border-slate-200 text-xs">
                                <div class="space-y-1">
                                    <div class="flex"><span class="w-24 text-slate-500 font-semibold">Nama</span>: <strong class="ml-1">{{ slip.name }}</strong></div>
                                    <div class="flex"><span class="w-24 text-slate-500 font-semibold">NIP</span>: <span class="ml-1 font-mono">{{ slip.nip }}</span></div>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex"><span class="w-24 text-slate-500 font-semibold">Jabatan</span>: <span class="ml-1">{{ slip.jabatan }}</span></div>
                                    <div class="flex"><span class="w-24 text-slate-500 font-semibold">Golongan</span>: <span class="ml-1">{{ slip.golongan }}</span></div>
                                </div>
                            </div>

                            <!-- Table of Components -->
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <h4 class="font-bold border-b pb-1 text-slate-700 mb-2">A. PENERIMAAN</h4>
                                    <div class="space-y-1.5">
                                        <div v-for="(e, idx) in slip.earnings" :key="idx" class="flex justify-between">
                                            <span class="text-slate-600">{{ e.label }}</span>
                                            <span class="font-bold">{{ formatRupiah(e.amount) }}</span>
                                        </div>
                                    </div>
                                    <div class="border-t pt-2 mt-3 flex justify-between font-black text-emerald-700">
                                        <span>Total Penerimaan:</span>
                                        <span>{{ formatRupiah(totalEarnings) }}</span>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="font-bold border-b pb-1 text-slate-700 mb-2">B. POTONGAN</h4>
                                    <div class="space-y-1.5">
                                        <div v-for="(d, idx) in slip.deductions" :key="idx" class="flex justify-between">
                                            <span class="text-slate-600">{{ d.label }}</span>
                                            <span class="font-bold text-rose-600">{{ formatRupiah(d.amount) }}</span>
                                        </div>
                                    </div>
                                    <div class="border-t pt-2 mt-3 flex justify-between font-black text-rose-700">
                                        <span>Total Potongan:</span>
                                        <span>{{ formatRupiah(totalDeductions) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Net Take Home -->
                            <div class="p-4 bg-emerald-50 rounded-lg border border-emerald-300 flex justify-between items-center text-sm">
                                <span class="font-bold text-emerald-900">GAJI BERSIH DITERIMA (TAKE HOME PAY):</span>
                                <span class="font-black text-emerald-900 text-lg">{{ formatRupiah(takeHome) }}</span>
                            </div>

                            <!-- Signatures & QR -->
                            <div class="grid grid-cols-3 gap-4 pt-6 text-center text-xs">
                                <div>
                                    <p class="text-slate-500">Penerima,</p>
                                    <div class="h-14"></div>
                                    <p class="font-bold">{{ slip.name }}</p>
                                </div>
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-slate-100 border border-slate-300 rounded flex items-center justify-center font-mono text-[9px] text-center p-1 font-bold">
                                        [QR VERIFIED SDAM]
                                    </div>
                                    <span class="text-[9px] text-slate-400 mt-1">Otentikasi Digital</span>
                                </div>
                                <div>
                                    <p class="text-slate-500">Bendahara Sekolah,</p>
                                    <div class="h-14"></div>
                                    <p class="font-bold">Ahmad Syukron, S.E.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Teleport>
        </div>
    </TeacherLayout>
</template>
