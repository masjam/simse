<script setup>
import BendaharaLayout from '@/Layouts/BendaharaLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

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

const formatCurrency = (amount) => {
    return 'Rp ' + Number(amount || 0).toLocaleString('id-ID');
};
</script>

<template>
    <Head title="Dashboard Keuangan" />
    <BendaharaLayout>
        <template #header-title><h1 class="text-2xl font-black text-slate-800">Dashboard Keuangan</h1></template>
        <template #header-subtitle><p class="text-slate-500 font-medium">Ringkasan arus kas, tagihan SPP, dompet siswa, dan penggajian</p></template>

        <div class="space-y-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white border border-slate-200 rounded-2xl p-6 relative overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Total Kas Aktif</p>
                            <h3 class="text-xl font-black text-slate-800 mt-1">{{ stats.total_kas }}</h3>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl p-6 relative overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">SPP Terkumpul (Bulan Ini)</p>
                            <h3 class="text-xl font-black text-slate-800 mt-1">{{ stats.spp_terkumpul }}</h3>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl p-6 relative overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Tunggakan SPP</p>
                            <h3 class="text-xl font-black text-rose-600 mt-1">{{ stats.tunggakan_spp }}</h3>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl p-6 relative overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Payroll Terbayar</p>
                            <h3 class="text-xl font-black text-slate-800 mt-1">{{ stats.payroll_terbayar }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Quick Actions -->
                <div class="lg:col-span-1 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                    <h3 class="text-lg font-black text-slate-800 mb-4">Aksi Cepat</h3>
                    <div class="space-y-3">
                        <Link :href="route('bendahara.kasir')" class="w-full flex items-center gap-4 p-4 rounded-xl border border-slate-100 hover:border-emerald-200 hover:bg-emerald-50 transition group">
                            <div class="w-10 h-10 bg-slate-50 group-hover:bg-emerald-100 rounded-lg flex items-center justify-center text-slate-500 group-hover:text-emerald-600 transition shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-slate-800 text-sm">Buka Kasir & Verifikasi</p>
                                <p class="text-xs text-slate-500">{{ stats.pending_verifications }} bukti bayar menunggu</p>
                            </div>
                        </Link>

                        <Link :href="route('bendahara.spp')" class="w-full flex items-center gap-4 p-4 rounded-xl border border-slate-100 hover:border-blue-200 hover:bg-blue-50 transition group">
                            <div class="w-10 h-10 bg-slate-50 group-hover:bg-blue-100 rounded-lg flex items-center justify-center text-slate-500 group-hover:text-blue-600 transition shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-slate-800 text-sm">Kelola Tagihan SPP</p>
                                <p class="text-xs text-slate-500">Terbitkan pos & tagihan massal</p>
                            </div>
                        </Link>

                        <Link :href="route('bendahara.dompet')" class="w-full flex items-center gap-4 p-4 rounded-xl border border-slate-100 hover:border-purple-200 hover:bg-purple-50 transition group">
                            <div class="w-10 h-10 bg-slate-50 group-hover:bg-purple-100 rounded-lg flex items-center justify-center text-slate-500 group-hover:text-purple-600 transition shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6a2 2 0 012-2h14a2 2 0 012 2v2H3V6zm0 4h18v8a2 2 0 01-2 2H5a2 2 0 01-2-2v-8z"></path></svg>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-slate-800 text-sm">Dompet Digital Siswa</p>
                                <p class="text-xs text-slate-500">Total mengendap {{ stats.total_dompet_siswa }}</p>
                            </div>
                        </Link>

                        <Link :href="route('bendahara.payroll')" class="w-full flex items-center gap-4 p-4 rounded-xl border border-slate-100 hover:border-amber-200 hover:bg-amber-50 transition group">
                            <div class="w-10 h-10 bg-slate-50 group-hover:bg-amber-100 rounded-lg flex items-center justify-center text-slate-500 group-hover:text-amber-600 transition shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-slate-800 text-sm">Proses Gaji Pegawai</p>
                                <p class="text-xs text-slate-500">{{ stats.draft_payrolls }} draft payroll belum cair</p>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-black text-slate-800">Transaksi Terbaru</h3>
                        <Link :href="route('bendahara.kasir')" class="text-sm font-bold text-emerald-600 hover:text-emerald-700">Buka Kasir</Link>
                    </div>

                    <div class="flex-1 overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs text-slate-500 uppercase font-bold border-b border-slate-100">
                                <tr>
                                    <th class="py-3 px-4">Keterangan</th>
                                    <th class="py-3 px-4">Tipe</th>
                                    <th class="py-3 px-4">Tanggal</th>
                                    <th class="py-3 px-4 text-right">Nominal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="trx in recentTransactions" :key="trx.id">
                                    <td class="py-3 px-4">
                                        <p class="font-bold text-slate-800">{{ trx.title }}</p>
                                        <p class="text-xs text-slate-500">{{ trx.subtitle }}</p>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold"
                                              :class="trx.category === 'Pemasukan' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">
                                            {{ trx.category }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-500">{{ trx.date }}</td>
                                    <td class="py-3 px-4 text-right font-black text-slate-800">{{ formatCurrency(trx.amount) }}</td>
                                </tr>
                                <tr v-if="recentTransactions.length === 0">
                                    <td colspan="4" class="py-8 text-center text-slate-400 text-sm">Belum ada transaksi keuangan tercatat.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BendaharaLayout>
</template>
